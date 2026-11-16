<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobListing;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJobsAndNewsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_board_lists_only_live_jobs(): void
    {
        JobListing::factory()->create(['title' => ['en' => 'Senior Accountant']]);
        JobListing::factory()->draft()->create(['title' => ['en' => 'Draft Designer']]);
        JobListing::factory()->create(['title' => ['en' => 'Closed Cashier'], 'closes_at' => now()->subDay()]);

        $this->get(route('jobs.index'))
            ->assertOk()
            ->assertSee('Senior Accountant')
            ->assertDontSee('Draft Designer')
            ->assertDontSee('Closed Cashier');
    }

    public function test_job_board_filters_by_keyword_category_type_and_remote(): void
    {
        $finance = JobCategory::factory()->create(['slug' => 'finance', 'name' => ['en' => 'Finance']]);
        JobListing::factory()->create(['title' => ['en' => 'Senior Accountant'], 'job_category_id' => $finance->id]);
        JobListing::factory()->create(['title' => ['en' => 'Barista'], 'employment_type' => EmploymentType::PartTime]);
        JobListing::factory()->create(['title' => ['en' => 'Laravel Developer'], 'is_remote' => true, 'company_id' => Company::factory()->create(['name' => 'Angkor Digital'])->id]);

        $this->get(route('jobs.index', ['q' => 'angkor']))->assertSee('Laravel Developer')->assertDontSee('Senior Accountant');
        $this->get(route('jobs.index', ['category' => 'finance']))->assertSee('Senior Accountant')->assertDontSee('Barista');
        $this->get(route('jobs.index', ['type' => 'part_time']))->assertSee('Barista')->assertDontSee('Senior Accountant');
        $this->get(route('jobs.index', ['remote' => '1']))->assertSee('Laravel Developer')->assertDontSee('Barista');
    }

    public function test_job_page_suggests_similar_jobs_from_the_same_category(): void
    {
        $category = JobCategory::factory()->create();
        $job = JobListing::factory()->create(['job_category_id' => $category->id]);
        JobListing::factory()->create(['title' => ['en' => 'Junior Accountant'], 'job_category_id' => $category->id]);
        JobListing::factory()->create(['title' => ['en' => 'Unrelated Chef']]);

        $this->get($job->publicUrl())
            ->assertOk()
            ->assertSee('Similar jobs')
            ->assertSee('Junior Accountant')
            ->assertDontSee('Unrelated Chef');
    }

    public function test_news_page_lists_live_articles_and_filters_by_category(): void
    {
        $sports = NewsCategory::factory()->create(['slug' => 'sports', 'name' => ['en' => 'Sports']]);
        NewsArticle::factory()->create(['title' => ['en' => 'Angkor Marathon breaks record'], 'news_category_id' => $sports->id, 'views_count' => 50]);
        NewsArticle::factory()->create(['title' => ['en' => 'Riel holds steady']]);
        NewsArticle::factory()->draft()->create(['title' => ['en' => 'Unpublished scoop']]);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee('Angkor Marathon breaks record')
            ->assertSee('Riel holds steady')
            ->assertSee('Most read')
            ->assertDontSee('Unpublished scoop');

        $this->get(route('news.index', ['category' => 'sports']))
            ->assertOk()
            ->assertSee('Angkor Marathon breaks record')
            ->assertDontSee('Riel holds steady');
    }

    public function test_article_page_shows_related_articles(): void
    {
        $category = NewsCategory::factory()->create();
        $article = NewsArticle::factory()->create(['news_category_id' => $category->id]);
        NewsArticle::factory()->create(['title' => ['en' => 'Another city story'], 'news_category_id' => $category->id]);

        $this->get($article->publicUrl())
            ->assertOk()
            ->assertSee('Keep reading')
            ->assertSee('Another city story');
    }

    public function test_homepage_shows_live_platform_counts(): void
    {
        $this->seed(CmsSeeder::class);
        JobListing::factory()->count(3)->create();
        JobListing::factory()->draft()->create();

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['3', 'Open jobs'])
            ->assertSee(route('jobs.index'));
    }
}
