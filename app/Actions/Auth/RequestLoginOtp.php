<?php

namespace App\Actions\Auth;

use App\Exceptions\OtpException;
use App\Services\Otp\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class RequestLoginOtp
{
    use AsAction;

    public function asController(Request $request, OtpService $otpService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile' => ['required', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'شماره موبایل را درست وارد کنید.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $otpService->request(
                mobile: (string) $request->string('mobile'),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (OtpException $exception) {
            return $this->otpErrorResponse($exception);
        } catch (Throwable) {
            return response()->json([
                'message' => 'ارسال کد تایید فعلا انجام نشد. چند دقیقه دیگر دوباره تلاش کنید.',
            ], 503);
        }

        return response()->json([
            'message' => 'کد تایید برای شماره موبایل شما ارسال شد.',
            'resend_after' => (int) config('otp.resend_seconds', 60),
            'expires_in' => (int) config('otp.ttl_minutes', 2) * 60,
        ]);
    }

    private function otpErrorResponse(OtpException $exception): JsonResponse
    {
        return match ($exception->reason) {
            'invalid_mobile' => response()->json([
                'message' => 'شماره موبایل را درست وارد کنید.',
            ], 422),
            'resend_too_soon' => response()->json([
                'message' => 'برای ارسال مجدد کد کمی صبر کنید.',
                'retry_after' => $exception->retryAfter,
            ], 429)->withHeaders([
                'Retry-After' => (string) $exception->retryAfter,
            ]),
            'too_many_requests' => response()->json([
                'message' => 'تعداد درخواست‌ها زیاد است. لطفا کمی بعد دوباره تلاش کنید.',
            ], 429),
            'sms_provider_not_configured' => response()->json([
                'message' => 'پنل پیامک هنوز کامل تنظیم نشده است.',
            ], 503),
            default => response()->json([
                'message' => 'ارسال کد تایید فعلا انجام نشد. چند دقیقه دیگر دوباره تلاش کنید.',
            ], 503),
        };
    }
}
