<?php

namespace Database\Seeders;

use App\Enums\ApplyMethod;
use App\Enums\CmsContentType;
use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Enums\VerificationStatus;
use App\Models\CmsContent;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Services\JobContentSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter jobs: categories, a few employers and the jobs shown on the homepage.
 *
 * The homepage's demo job items are replaced by real job listings, which then appear
 * on the homepage and in search through the automatic sync. Only fills empty areas.
 */
class JobsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        if (JobListing::query()->exists()) {
            return;
        }

        // Demo homepage job items that were not generated from a job listing.
        CmsContent::where('content_type', CmsContentType::Job)->whereNull('source_type')->delete();

        $sync = app(JobContentSync::class);

        foreach ($this->starterJobs() as $index => $item) {
            $company = Company::firstOrCreate(['slug' => Str::slug($item['company'])], [
                'name' => $item['company'],
                'description' => ['en' => $item['about'], 'km' => null],
                'city' => $item['city'],
                'verification_status' => $item['verified'] ? VerificationStatus::Verified : VerificationStatus::Unverified,
                'verified_at' => $item['verified'] ? now() : null,
                'is_active' => true,
            ]);

            $job = JobListing::create([
                'company_id' => $company->id,
                'job_category_id' => $categories[$item['category']]->id,
                'slug' => Str::slug($item['title'].' '.$item['company']),
                'title' => ['en' => $item['title'], 'km' => null],
                'description' => ['en' => $item['description'], 'km' => null],
                'employment_type' => $item['type'],
                'location' => $item['location'],
                'is_remote' => $item['remote'] ?? false,
                'salary_min' => $item['salary'][0],
                'salary_max' => $item['salary'][1],
                'salary_currency' => 'USD',
                'salary_period' => $item['period'] ?? 'month',
                'apply_method' => $item['apply'] ?? ApplyMethod::OnSite,
                'apply_url' => $item['apply_url'] ?? null,
                'apply_email' => $item['apply_email'] ?? null,
                'status' => JobStatus::Published,
                'published_at' => now()->subDays($index + 1),
                'closes_at' => now()->addDays(30 - $index),
                'is_featured' => $index === 0,
                'featured_until' => $index === 0 ? now()->addDays(14) : null,
            ]);

            // DatabaseSeeder disables model events, so sync explicitly.
            $sync->sync($job);
        }
    }

    /**
     * @return array<string, JobCategory>
     */
    private function seedCategories(): array
    {
        $categories = [
            'accounting' => ['en' => 'Accounting & Finance', 'km' => 'គណនេយ្យ និងហិរញ្ញវត្ថុ'],
            'it' => ['en' => 'IT & Software', 'km' => 'ព័ត៌មានវិទ្យា'],
            'hospitality' => ['en' => 'Hotel & Tourism', 'km' => 'សណ្ឋាគារ និងទេសចរណ៍'],
            'marketing' => ['en' => 'Marketing & Sales', 'km' => 'ទីផ្សារ និងលក់'],
            'logistics' => ['en' => 'Logistics & Transport', 'km' => 'ដឹកជញ្ជូន'],
            'education' => ['en' => 'Education & Training', 'km' => 'អប់រំ និងបណ្តុះបណ្តាល'],
            'admin' => ['en' => 'Administration', 'km' => 'រដ្ឋបាល'],
            'engineering' => ['en' => 'Engineering & Construction', 'km' => 'វិស្វកម្ម និងសំណង់'],
        ];

        $position = 0;

        return collect($categories)->mapWithKeys(fn (array $name, string $slug): array => [
            $slug => JobCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $position++]),
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function starterJobs(): array
    {
        return [
            [
                'title' => 'Senior Accountant', 'company' => 'Mekong Retail Group', 'city' => 'Phnom Penh', 'verified' => true,
                'about' => 'A growing retail group with supermarkets across Cambodia.',
                'category' => 'accounting', 'type' => EmploymentType::FullTime, 'location' => 'Phnom Penh', 'salary' => [900, 1300],
                'description' => "Lead monthly closing and reporting for a growing retail group.\n\n## Responsibilities\n\n- Prepare monthly financial statements\n- Manage tax filings with the GDT\n- Support the annual audit\n\n## Requirements\n\n- Degree in accounting or finance\n- 3+ years of experience\n- Good English",
            ],
            [
                'title' => 'Frontend Developer', 'company' => 'Angkor Digital', 'city' => 'Phnom Penh', 'verified' => true,
                'about' => 'Web, mobile and cloud solutions for Cambodian businesses.',
                'category' => 'it', 'type' => EmploymentType::FullTime, 'location' => 'Phnom Penh', 'remote' => true, 'salary' => [1200, 1800],
                'apply' => ApplyMethod::ExternalUrl, 'apply_url' => 'https://example.com/careers/frontend-developer',
                'description' => "Build modern web interfaces with a product team.\n\n## What you will do\n\n- Build responsive pages with Vue or React\n- Work closely with designers\n\n## Requirements\n\n- 2+ years with JavaScript\n- Experience with Tailwind CSS is a plus",
            ],
            [
                'title' => 'Hotel Front Office Supervisor', 'company' => 'Temple View Resort', 'city' => 'Siem Reap', 'verified' => true,
                'about' => 'Boutique resort minutes from Angkor Wat.',
                'category' => 'hospitality', 'type' => EmploymentType::FullTime, 'location' => 'Siem Reap', 'salary' => [600, 800],
                'description' => "Welcome guests and lead the reception team.\n\n- Supervise check-in and check-out\n- Train new front office staff\n- Handle guest requests in English",
            ],
            [
                'title' => 'Marketing Executive', 'company' => 'Khmer Coffee Co.', 'city' => 'Phnom Penh', 'verified' => false,
                'about' => 'Specialty coffee roaster and café chain serving Cambodian beans.',
                'category' => 'marketing', 'type' => EmploymentType::FullTime, 'location' => 'Phnom Penh', 'salary' => [550, 750],
                'apply' => ApplyMethod::Email, 'apply_email' => 'jobs@example.com',
                'description' => "Plan campaigns across social and digital channels.\n\n- Run our Facebook and TikTok pages\n- Plan in-store promotions\n- Report on campaign results",
            ],
            [
                'title' => 'Logistics Coordinator', 'company' => 'Sihanoukville Port Logistics', 'city' => 'Sihanoukville', 'verified' => false,
                'about' => 'Freight and warehousing services at Sihanoukville port.',
                'category' => 'logistics', 'type' => EmploymentType::Contract, 'location' => 'Sihanoukville', 'salary' => [700, 950],
                'description' => "Coordinate shipments between port and warehouses.\n\n- Track containers and delivery schedules\n- Prepare shipping documents\n- Work with customs brokers",
            ],
            [
                'title' => 'English Teacher', 'company' => 'Bright Future School', 'city' => 'Battambang', 'verified' => false,
                'about' => 'A friendly language school for young learners.',
                'category' => 'education', 'type' => EmploymentType::PartTime, 'location' => 'Battambang', 'salary' => [12, 12], 'period' => 'hour',
                'description' => "Teach young learners in a friendly language school.\n\n- Afternoon and weekend classes\n- Teaching materials provided\n- TEFL certificate preferred",
            ],
        ];
    }
}
