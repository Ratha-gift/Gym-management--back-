<?php

namespace Database\Seeders;

use App\Models\Membership;
use App\Models\MembershipPackage;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::create([
            'role_name' => 'Super Admin',
            'description' => 'Full access to every module.',
        ]);
        Role::create(['role_name' => 'Staff', 'description' => 'Front-desk staff.']);

        $this->call(PermissionSeeder::class);

        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@gympro.test',
            'phone' => '012 345 678',
            'password' => bcrypt('password'),
            'status' => 'active',
            'role_id' => $adminRole->role_id,
        ]);

        $packages = collect([
            ['package_name' => 'Basic Monthly', 'duration_type' => 'months', 'duration_value' => 1, 'price' => 25],
            ['package_name' => 'Quarterly', 'duration_type' => 'months', 'duration_value' => 3, 'price' => 65],
            ['package_name' => 'Annual', 'duration_type' => 'months', 'duration_value' => 12, 'price' => 220],
            ['package_name' => 'Day Pass', 'duration_type' => 'days', 'duration_value' => 1, 'price' => 5],
            ['package_name' => 'Student Monthly', 'duration_type' => 'months', 'duration_value' => 1, 'price' => 18],
        ])->map(fn ($pkg) => MembershipPackage::create($pkg + ['status' => 'active']));

        $members = collect([
            ['first_name' => 'Dara', 'last_name' => 'Long', 'phone' => '012 345 678', 'status' => 'active'],
            ['first_name' => 'Sreynich', 'last_name' => 'Mean', 'phone' => '098 765 432', 'status' => 'active'],
            ['first_name' => 'Vuthy', 'last_name' => 'Sok', 'phone' => '010 222 333', 'status' => 'active'],
            ['first_name' => 'Pich', 'last_name' => 'Sreymom', 'phone' => '015 555 666', 'status' => 'active'],
            ['first_name' => 'Kosal', 'last_name' => 'Chhay', 'phone' => '071 777 888', 'status' => 'active'],
        ])->values();

        foreach ($members as $index => $member) {
            $record = Member::create($member + [
                'member_code' => 'MEM' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'qr_code' => (string) Str::uuid(),
            ]);

            // First 3 get an active membership, last 2 an expired one.
            $isExpired = $index >= 3;

            Membership::create([
                'member_id' => $record->member_id,
                'package_id' => $packages->random()->package_id,
                'start_date' => $isExpired ? now()->subMonths(2) : now()->subDays(5),
                'end_date' => $isExpired ? now()->subDays(3) : now()->addMonth(),
                'status' => $isExpired ? 'expired' : 'active',
            ]);
        }

        // Bulk out the member roster so dashboard totals look realistic.
        Member::factory(495)->create();
    }
}
