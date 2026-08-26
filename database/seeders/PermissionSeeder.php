<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seeds a starter permission set grouped by module and assigns sensible
     * defaults to the two seeded roles (Super Admin gets everything, Staff
     * gets day-to-day front-desk actions only). Uses firstOrCreate/sync
     * throughout, so it's safe to re-run on its own via
     * `php artisan db:seed --class=PermissionSeeder` without touching or
     * duplicating any other data.
     */
    public function run(): void
    {
        $modules = [
            'Members' => ['View Members', 'Create Members', 'Edit Members', 'Delete Members'],
            'Packages' => ['View Packages', 'Create Packages', 'Edit Packages', 'Delete Packages'],
            'Membership' => ['View Membership', 'Create Membership', 'Edit Membership', 'Delete Membership'],
            'Attendance' => ['View Attendance', 'Check In / Check Out'],
            'Payments' => ['View Payments', 'Record Payments'],
            'Reports' => ['View Reports'],
            'Settings' => ['Manage Settings'],
            'Roles' => ['Manage Roles & Permissions'],
        ];

        $permissions = collect();
        foreach ($modules as $module => $names) {
            foreach ($names as $name) {
                $permissions->push(
                    Permission::firstOrCreate(['permission_name' => $name, 'module' => $module])
                );
            }
        }

        if ($admin = Role::where('role_name', 'Super Admin')->first()) {
            $admin->permissions()->sync($permissions->pluck('permission_id'));
        }

        if ($staff = Role::where('role_name', 'Staff')->first()) {
            $staffPermissionNames = [
                'View Members', 'Create Members', 'Edit Members',
                'View Packages',
                'View Membership', 'Create Membership', 'Edit Membership',
                'View Attendance', 'Check In / Check Out',
                'View Payments', 'Record Payments',
                'View Reports',
            ];
            $staffIds = $permissions->whereIn('permission_name', $staffPermissionNames)->pluck('permission_id');
            $staff->permissions()->sync($staffIds);
        }
    }
}
