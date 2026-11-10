<?php

namespace Database\Factories;

use App\Enums\VerificationStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => ['en' => fake()->sentence(12), 'km' => null],
            'email' => fake()->companyEmail(),
            'city' => 'Phnom Penh',
            'verification_status' => VerificationStatus::Unverified,
            'is_active' => true,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => ['verification_status' => VerificationStatus::Verified, 'verified_at' => now()]);
    }
}
