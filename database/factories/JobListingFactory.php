<?php

namespace Database\Factories;

use App\Enums\ApplyMethod;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state: a published, on-site-apply job.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->jobTitle();

        return [
            'company_id' => Company::factory(),
            'job_category_id' => JobCategory::factory(),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'title' => ['en' => $title, 'km' => null],
            'description' => ['en' => fake()->paragraphs(2, true), 'km' => null],
            'employment_type' => EmploymentType::FullTime,
            'location' => 'Phnom Penh',
            'salary_min' => 600,
            'salary_max' => 900,
            'salary_currency' => 'USD',
            'salary_period' => 'month',
            'apply_method' => ApplyMethod::OnSite,
            'status' => JobStatus::Published,
            'published_at' => now()->subHour(),
            'closes_at' => now()->addDays(30),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => JobStatus::Draft, 'published_at' => null]);
    }
}
