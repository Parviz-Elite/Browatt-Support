<?php

namespace App\Services\Otp;

use App\Contracts\SmsProvider;
use App\Exceptions\OtpException;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Throwable;

class OtpService
{
    public function __construct(
        private readonly SmsProvider $smsProvider,
    ) {}

    public function request(string $mobile, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $mobile = $this->normalizeMobile($mobile);

        $this->ensureRequestIsAllowed($mobile, $ipAddress);
        $this->ensureResendWindowHasPassed($mobile);

        $code = $this->generateCode();

        $otp = OtpCode::query()->create([
            'mobile' => $mobile,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('otp.ttl_minutes', 2)),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        try {
            $this->smsProvider->sendOtp($mobile, $code);
        } catch (Throwable $exception) {
            $otp->delete();

            throw $exception;
        }
    }

    public function verify(string $mobile, string $code, ?string $ipAddress = null): User
    {
        $mobile = $this->normalizeMobile($mobile);
        $code = $this->normalizeDigits($code);

        $this->ensureVerificationIsAllowed($mobile, $ipAddress);

        return DB::transaction(function () use ($mobile, $code) {
            /** @var OtpCode|null $otp */
            $otp = OtpCode::query()
                ->where('mobile', $mobile)
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($otp === null) {
                throw OtpException::invalidCode();
            }

            if ($otp->attempts >= (int) config('otp.max_attempts', 5)) {
                throw OtpException::maxAttemptsReached();
            }

            $otp->increment('attempts');
            $otp->refresh();

            if (! Hash::check($code, $otp->code_hash)) {
                throw OtpException::invalidCode();
            }

            $otp->forceFill([
                'used_at' => now(),
            ])->save();

            $user = User::withTrashed()->firstOrCreate(
                ['mobile' => $mobile],
                ['registered_at' => now()]
            );

            if ($user->trashed()) {
                $user->restore();
            }

            if ($user->registered_at === null) {
                $user->forceFill([
                    'registered_at' => now(),
                ])->save();
            }

            $customerRole = Role::query()->firstOrCreate([
                'name' => 'customer',
                'guard_name' => 'web',
            ]);

            if (! $user->hasAnyRole(['general_manager', $customerRole->name])) {
                $user->assignRole($customerRole);
            }

            return $user;
        });
    }

    public function normalizeMobile(string $mobile): string
    {
        $mobile = $this->normalizeDigits($mobile);
        $mobile = preg_replace('/[\s\-()]/', '', $mobile) ?? '';

        if (str_starts_with($mobile, '+98')) {
            $mobile = '0'.substr($mobile, 3);
        } elseif (str_starts_with($mobile, '0098')) {
            $mobile = '0'.substr($mobile, 4);
        } elseif (str_starts_with($mobile, '98') && strlen($mobile) === 12) {
            $mobile = '0'.substr($mobile, 2);
        }

        if (! preg_match('/^09\d{9}$/', $mobile)) {
            throw OtpException::invalidMobile();
        }

        return $mobile;
    }

    private function ensureRequestIsAllowed(string $mobile, ?string $ipAddress): void
    {
        foreach ($this->rateLimitKeys($mobile, $ipAddress) as $key => $limit) {
            if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
                throw OtpException::tooManyRequests();
            }

            RateLimiter::hit($key, $limit['decay']);
        }
    }

    private function ensureVerificationIsAllowed(string $mobile, ?string $ipAddress): void
    {
        foreach ($this->verificationRateLimitKeys($mobile, $ipAddress) as $key => $limit) {
            if (RateLimiter::tooManyAttempts($key, $limit['max'])) {
                throw OtpException::tooManyRequests();
            }

            RateLimiter::hit($key, $limit['decay']);
        }
    }

    private function ensureResendWindowHasPassed(string $mobile): void
    {
        $latestOtp = OtpCode::query()
            ->where('mobile', $mobile)
            ->whereNull('used_at')
            ->latest('id')
            ->first();

        if ($latestOtp === null) {
            return;
        }

        $resendSeconds = (int) config('otp.resend_seconds', 60);
        $availableAt = $latestOtp->created_at->copy()->addSeconds($resendSeconds);

        if ($availableAt->isFuture()) {
            throw OtpException::resendTooSoon((int) ceil(now()->diffInSeconds($availableAt)));
        }
    }

    /**
     * @return array<string, array{max: int, decay: int}>
     */
    private function rateLimitKeys(string $mobile, ?string $ipAddress): array
    {
        $limits = [
            'otp:mobile:'.$mobile => [
                'max' => (int) config('otp.rate_limits.mobile.max_attempts', 5),
                'decay' => (int) config('otp.rate_limits.mobile.decay_seconds', 3600),
            ],
        ];

        if ($ipAddress !== null && $ipAddress !== '') {
            $limits['otp:ip:'.$ipAddress] = [
                'max' => (int) config('otp.rate_limits.ip.max_attempts', 30),
                'decay' => (int) config('otp.rate_limits.ip.decay_seconds', 3600),
            ];
        }

        return $limits;
    }

    /**
     * @return array<string, array{max: int, decay: int}>
     */
    private function verificationRateLimitKeys(string $mobile, ?string $ipAddress): array
    {
        $limits = [
            'otp-verify:mobile:'.$mobile => [
                'max' => (int) config('otp.verify_rate_limits.mobile.max_attempts', 10),
                'decay' => (int) config('otp.verify_rate_limits.mobile.decay_seconds', 600),
            ],
        ];

        if ($ipAddress !== null && $ipAddress !== '') {
            $limits['otp-verify:ip:'.$ipAddress] = [
                'max' => (int) config('otp.verify_rate_limits.ip.max_attempts', 60),
                'decay' => (int) config('otp.verify_rate_limits.ip.decay_seconds', 3600),
            ];
        }

        return $limits;
    }

    private function generateCode(): string
    {
        $length = (int) config('otp.code_length', 6);
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr(trim($value), $this->digitMap());
    }

    /**
     * @return array<string, string>
     */
    private function digitMap(): array
    {
        return [
            json_decode('"\u06f0"') => '0',
            json_decode('"\u06f1"') => '1',
            json_decode('"\u06f2"') => '2',
            json_decode('"\u06f3"') => '3',
            json_decode('"\u06f4"') => '4',
            json_decode('"\u06f5"') => '5',
            json_decode('"\u06f6"') => '6',
            json_decode('"\u06f7"') => '7',
            json_decode('"\u06f8"') => '8',
            json_decode('"\u06f9"') => '9',
            json_decode('"\u0660"') => '0',
            json_decode('"\u0661"') => '1',
            json_decode('"\u0662"') => '2',
            json_decode('"\u0663"') => '3',
            json_decode('"\u0664"') => '4',
            json_decode('"\u0665"') => '5',
            json_decode('"\u0666"') => '6',
            json_decode('"\u0667"') => '7',
            json_decode('"\u0668"') => '8',
            json_decode('"\u0669"') => '9',
        ];
    }
}
