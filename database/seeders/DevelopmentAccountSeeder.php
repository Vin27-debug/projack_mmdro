<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DevelopmentAccountSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super-admin')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $driverRole = Role::where('name', 'driver')->firstOrFail();

        $superAdmin = User::updateOrCreate(
            ['email' => env('MUNIRESQ_DEV_SUPER_ADMIN_EMAIL', 'super-admin@muniresq.test')],
            [
                'name' => 'MuniResQ Development Super Admin',
                'password' => Hash::make(env('MUNIRESQ_DEV_SUPER_ADMIN_PASSWORD', 'MuniResQ-Dev-Super-2026!')),
                'status' => 'approved',
                'employee_id' => 'DEV-SUPER-001',
                'position' => 'Development Super Administrator',
                'department' => 'MuniResQ Development',
                'office' => 'Local Development',
                'contact_number' => '09000000001',
                'approved_at' => now(),
            ]
        );
        $superAdmin->syncRoles([$superAdminRole]);

        $admin = User::updateOrCreate(
            ['email' => env('MUNIRESQ_DEV_ADMIN_EMAIL', 'admin@muniresq.test')],
            [
                'name' => 'MuniResQ Development Admin',
                'password' => Hash::make(env('MUNIRESQ_DEV_ADMIN_PASSWORD', 'MuniResQ-Dev-Admin-2026!')),
                'status' => 'approved',
                'employee_id' => 'DEV-ADMIN-001',
                'position' => 'Development Administrator',
                'department' => 'MuniResQ Development',
                'office' => 'Local Development',
                'contact_number' => '09000000002',
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
            ]
        );
        $admin->syncRoles([$adminRole]);

        $driverUser = User::updateOrCreate(
            ['email' => env('MUNIRESQ_DEV_DRIVER_EMAIL', 'driver@muniresq.test')],
            [
                'name' => 'MuniResQ Development Driver',
                'password' => Hash::make(env('MUNIRESQ_DEV_DRIVER_PASSWORD', 'MuniResQ-Dev-Driver-2026!')),
                'status' => 'approved',
                'badge_id' => 'DEV-DRIVER-001',
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
            ]
        );
        $driverUser->syncRoles([$driverRole]);

        Driver::updateOrCreate(
            ['user_id' => $driverUser->id],
            [
                'badge_id' => 'DEV-DRIVER-001',
                'contact_number' => '09000000003',
                'license_number' => 'DEV-LICENSE-001',
                'license_expiry' => now()->addYears(2)->toDateString(),
                'status' => Driver::STATUS_AVAILABLE,
            ]
        );
    }
}
