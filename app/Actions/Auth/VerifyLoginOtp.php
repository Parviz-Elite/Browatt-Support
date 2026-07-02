<?php

namespace App\Actions\Auth;

use App\Exceptions\OtpException;
use App\Services\Otp\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;

class VerifyLoginOtp
{
    use AsAction;

    public function asController(Request $request, OtpService $otpService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'min:5', 'max:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'کد تایید را درست وارد کنید.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $otpService->verify(
                mobile: (string) $request->string('mobile'),
                code: (string) $request->string('code'),
                ipAddress: $request->ip(),
            );
        } catch (OtpException $exception) {
            return $this->otpErrorResponse($exception);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'ورود با موفقیت انجام شد.',
            'redirect' => route('dashboard'),
        ]);
    }

    private function otpErrorResponse(OtpException $exception): JsonResponse
    {
        return match ($exception->reason) {
            'invalid_mobile', 'invalid_code' => response()->json([
                'message' => 'کد تایید نادرست یا منقضی شده است.',
            ], 422),
            'max_attempts_reached' => response()->json([
                'message' => 'تعداد تلاش برای این کد زیاد شده است. کد جدید دریافت کنید.',
            ], 429),
            'too_many_requests' => response()->json([
                'message' => 'تعداد تلاش‌ها زیاد است. لطفا کمی بعد دوباره تلاش کنید.',
            ], 429),
            default => response()->json([
                'message' => 'بررسی کد تایید فعلا انجام نشد. چند دقیقه دیگر دوباره تلاش کنید.',
            ], 503),
        };
    }
}
