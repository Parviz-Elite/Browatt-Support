<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed application roles and initial global managers.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $generalManagerRole = Role::query()->firstOrCreate([
            'name' => 'general_manager',
            'guard_name' => 'web',
        ]);

        Role::query()->firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        foreach ($this->generalManagers() as $manager) {
            $user = User::withTrashed()->updateOrCreate(
                ['mobile' => $manager['mobile']],
                [
                    'first_name' => $manager['first_name'],
                    'last_name' => $manager['last_name'],
                    'registered_at' => now(),
                    'deleted_at' => null,
                ]
            );

            $user->assignRole($generalManagerRole);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, array{mobile: string, first_name: string, last_name: string}>
     */
    private function generalManagers(): array
    {
        return [
            [
                'mobile' => '09144004385',
                'first_name' => 'مهدی',
                'last_name' => 'مکاریان',
            ],
            [
                'mobile' => '09900940019',
                'first_name' => 'پرویز',
                'last_name' => 'الیاس زاده',
            ],
            [
                'mobile' => '09148064984',
                'first_name' => 'مهرداد',
                'last_name' => 'مهردادی',
            ],
            [
                'mobile' => '09146585966',
                'first_name' => 'مجید',
                'last_name' => 'نوروزی',
            ],
        ];
    }
}
