<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'mobile' => $user->mobile,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => trim(implode(' ', array_filter([$user->first_name, $user->last_name]))) ?: $user->mobile,
                    'roles' => $user->getRoleNames()->values()->all(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
                    'is_manager' => $user->hasRole('general_manager') || $user->can('roles.manage') || $user->can('users.view_any') || $user->can('customers.view_any') || $user->can('warranties.view_any') || $user->can('settings.manage'),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
