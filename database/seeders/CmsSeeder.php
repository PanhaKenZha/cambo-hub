<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\HomepageSectionType;
use App\Enums\MenuLocation;
use App\Models\HomepageSection;
use App\Models\MenuItem;
use App\Models\Page;
use App\Services\SiteContent;
use Illuminate\Database\Seeder;

/**
 * Starter website content: standard pages, homepage layout and menus.
 * Only fills empty areas, so it never overwrites what administrators have built.
 */
class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $pages = $this->seedPages();
        $this->seedHomepage();
        $this->seedMenus($pages);

        // DatabaseSeeder disables model events, so refresh the public content cache explicitly.
        app(SiteContent::class)->flush();
    }

    /**
     * @return array<string, Page>
     */
    private function seedPages(): array
    {
        $pages = [
            'about-us' => [
                'title' => ['en' => 'About us', 'km' => 'អំពីយើង'],
                'body' => "## Cambodia's all-in-one platform\n\nCambo Hub brings news, jobs, shopping, businesses, property, hosting, courses and events together in one place.\n\n## Our mission\n\nMake everyday services in Cambodia easier to find and use — in Khmer and English.",
            ],
            'contact' => [
                'title' => ['en' => 'Contact us', 'km' => 'ទំនាក់ទំនង'],
                'body' => "We're happy to help.\n\n- **Email:** hello@cambohub.com\n- **Address:** Phnom Penh, Cambodia\n\nOur team replies within one working day.",
            ],
            'terms' => [
                'title' => ['en' => 'Terms of service', 'km' => 'លក្ខខណ្ឌប្រើប្រាស់'],
                'body' => "## Using Cambo Hub\n\nBy using Cambo Hub you agree to these terms. Replace this text with your full terms of service before launch.",
            ],
            'privacy' => [
                'title' => ['en' => 'Privacy policy', 'km' => 'គោលការណ៍ឯកជនភាព'],
                'body' => "## Your privacy\n\nThis page explains what information we collect and how we use it. Replace this text with your full privacy policy before launch.",
            ],
        ];

        return collect($pages)->mapWithKeys(fn (array $content, string $slug): array => [$slug => Page::firstOrCreate(['slug' => $slug], [
            'title' => $content['title'],
            'body' => ['en' => $content['body'], 'km' => null],
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ])])->all();
    }

    private function seedHomepage(): void
    {
        if (HomepageSection::query()->exists()) {
            return;
        }

        $sections = [
            [HomepageSectionType::Hero, ['en' => 'Everything Cambodia, in one place', 'km' => 'អ្វីៗគ្រប់យ៉ាងនៅកម្ពុជា ក្នុងកន្លែងតែមួយ'], ['en' => 'News, jobs, shopping and services for Cambodia.', 'km' => null]],
            [HomepageSectionType::FeaturedNews, ['en' => 'Top stories', 'km' => 'ព័ត៌មានសំខាន់ៗ'], null],
            [HomepageSectionType::LatestJobs, ['en' => 'Latest jobs', 'km' => 'ការងារថ្មីៗ'], null],
            [HomepageSectionType::MarketplaceHighlights, ['en' => 'Shop the marketplace', 'km' => 'ទីផ្សារ'], null],
            [HomepageSectionType::FeaturedBusinesses, ['en' => 'Featured businesses', 'km' => 'អាជីវកម្មលេចធ្លោ'], null],
            [HomepageSectionType::LatestListings, ['en' => 'Property & vehicles', 'km' => 'អចលនទ្រព្យ និងយានយន្ត'], null],
            [HomepageSectionType::UpcomingEvents, ['en' => 'Upcoming events', 'km' => 'ព្រឹត្តិការណ៍ខាងមុខ'], null],
            [HomepageSectionType::PopularCourses, ['en' => 'Popular courses', 'km' => 'វគ្គសិក្សាពេញនិយម'], null],
        ];

        foreach ($sections as $position => [$type, $title, $subtitle]) {
            HomepageSection::create([
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'item_count' => 6,
                'is_visible' => true,
                'sort_order' => $position,
            ]);
        }
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedMenus(array $pages): void
    {
        if (! MenuItem::where('location', MenuLocation::Header)->exists()) {
            $headerLinks = [
                [['en' => 'News', 'km' => 'ព័ត៌មាន'], '/news'],
                [['en' => 'Jobs', 'km' => 'ការងារ'], '/jobs'],
                [['en' => 'Marketplace', 'km' => 'ទីផ្សារ'], '/marketplace'],
                [['en' => 'Businesses', 'km' => 'អាជីវកម្ម'], '/businesses'],
                [['en' => 'Events', 'km' => 'ព្រឹត្តិការណ៍'], '/events'],
            ];

            foreach ($headerLinks as $position => [$label, $url]) {
                MenuItem::create(['location' => MenuLocation::Header, 'label' => $label, 'url' => $url, 'sort_order' => $position]);
            }
        }

        if (! MenuItem::where('location', MenuLocation::Footer)->exists()) {
            $company = MenuItem::create(['location' => MenuLocation::Footer, 'label' => ['en' => 'Company', 'km' => 'ក្រុមហ៊ុន'], 'url' => '#', 'sort_order' => 0]);
            $legal = MenuItem::create(['location' => MenuLocation::Footer, 'label' => ['en' => 'Legal', 'km' => 'ច្បាប់'], 'url' => '#', 'sort_order' => 1]);

            foreach ([[$company, 'about-us'], [$company, 'contact'], [$legal, 'terms'], [$legal, 'privacy']] as $position => [$parent, $slug]) {
                MenuItem::create([
                    'location' => MenuLocation::Footer,
                    'parent_id' => $parent->id,
                    'label' => $pages[$slug]->title,
                    'page_id' => $pages[$slug]->id,
                    'sort_order' => $position,
                ]);
            }
        }
    }
}
