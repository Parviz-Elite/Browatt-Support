<?php

namespace App\Actions\Users;

use App\Exceptions\OtpException;
use App\Models\User;
use App\Services\Otp\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    public function asController(Request $request, OtpService $otpService): RedirectResponse
    {
        $isGeneralManager = $request->user()->hasRole('general_manager');

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'mobile' => ['required', 'string', 'max:32'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'required', 'string', 'distinct',
                Rule::exists('roles', 'name')
                    ->where('guard_name', 'web')
                    ->whereNot('name', 'customer')
                    ->when(! $isGeneralManager, fn ($rule) => $rule->whereNot('name', 'general_manager')),
            ],
        ], [
            'roles.*.exists' => 'نقش انتخاب‌شده برای افزودن کاربر مجاز نیست.',
        ], [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'mobile' => 'موبایل',
            'roles' => 'نقش‌ها',
        ]);

        try {
            $validated['mobile'] = $otpService->normalizeMobile($validated['mobile']);
        } catch (OtpException) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره موبایل معتبر وارد کنید؛ مانند 09123456789.',
            ]);
        }

        Validator::make($validated, [
            'mobile' => [Rule::unique('users', 'mobile')],
        ], [
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        ])->validate();

        DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'mobile' => $validated['mobile'],
                'registered_at' => now(),
            ]);

            $user->syncRoles($validated['roles']);
        });

        return to_route('admin.users.index')->with('success', 'کاربر جدید با موفقیت افزوده شد.');
    }
}
