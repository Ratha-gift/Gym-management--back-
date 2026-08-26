<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected static int $sequence = 6;

    public function definition(): array
    {
        return [
            'member_code' => 'MEM' . str_pad((string) static::$sequence++, 3, '0', STR_PAD_LEFT),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-16 years'),
            'phone' => fake()->numerify('0## ### ###'),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'qr_code' => (string) Str::uuid(),
            'status' => 'active',
        ];
    }

    public function configure(): static
    {
        // ~86% of seeded members get an active membership, ~14% an expired one —
        // mirrors the 430/70 split the dashboard mock was designed around.
        return $this->afterCreating(function (Member $member) {
            $isExpired = fake()->boolean(14);
            $package = MembershipPackage::inRandomOrder()->first();

            if (! $package) {
                return;
            }

            Membership::create([
                'member_id' => $member->member_id,
                'package_id' => $package->package_id,
                'start_date' => $isExpired ? now()->subMonths(3) : now()->subDays(fake()->numberBetween(1, 25)),
                'end_date' => $isExpired ? now()->subDays(fake()->numberBetween(1, 20)) : now()->addDays(fake()->numberBetween(5, 60)),
                'status' => $isExpired ? 'expired' : 'active',
            ]);
        });
    }
}
