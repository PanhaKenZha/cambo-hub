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

    /**
     * Every section the homepage brief asks for, in display order.
     */
    private function seedHomepage(): void
    {
        if (HomepageSection::query()->exists()) {
            return;
        }

        $sections = [
            [HomepageSectionType::Hero, ['en' => 'Your everyday Cambodia. One connected place.', 'km' => 'កម្ពុជាប្រចាំថ្ងៃរបស់អ្នក។ កន្លែងតែមួយដែលភ្ជាប់គ្នា។'], ['en' => 'News, jobs, shopping, businesses, property, events, and community — discover everything Cambodia has to offer with one account.', 'km' => null], ['image_path' => 'images/backgrounds/cambodia-platform.webp']],
            [HomepageSectionType::ServiceCategories, ['en' => 'Explore our services', 'km' => 'ស្វែងយល់ពីសេវាកម្មរបស់យើង'], ['en' => 'Every Cambo Hub service, one tap away.', 'km' => null], ['item_count' => 12]],
            [HomepageSectionType::Stories, ['en' => 'Stories', 'km' => 'រឿងរ៉ាវ'], null, ['item_count' => 10]],
            [HomepageSectionType::PromotionalBanners, ['en' => 'Promotions', 'km' => 'ការផ្សព្វផ្សាយ'], null, ['item_count' => 2]],
            [HomepageSectionType::FeaturedNews, ['en' => 'Latest news', 'km' => 'ព័ត៌មានថ្មីៗ'], ['en' => 'Headlines from across Cambodia.', 'km' => null], ['item_count' => 4, 'button_label' => ['en' => 'All news', 'km' => null], 'link_url' => '/search?type=news']],
            [HomepageSectionType::LatestJobs, ['en' => 'Featured jobs', 'km' => 'ការងារពិសេស'], ['en' => 'Opportunities from trusted Cambodian employers.', 'km' => null], ['button_label' => ['en' => 'All jobs', 'km' => null], 'link_url' => '/search?type=job']],
            [HomepageSectionType::MarketplaceHighlights, ['en' => 'Popular products', 'km' => 'ផលិតផលពេញនិយម'], ['en' => 'What people across Cambodia are buying this week.', 'km' => null], ['item_count' => 8, 'button_label' => ['en' => 'All products', 'km' => null], 'link_url' => '/search?type=product']],
            [HomepageSectionType::Advertisements, ['en' => 'Advertisements', 'km' => 'ការផ្សាយពាណិជ្ជកម្ម'], null, ['item_count' => 2]],
            [HomepageSectionType::FeaturedBusinesses, ['en' => 'Business listings', 'km' => 'បញ្ជីអាជីវកម្ម'], ['en' => 'Discover trusted shops, services, and companies near you.', 'km' => null], ['button_label' => ['en' => 'All businesses', 'km' => null], 'link_url' => '/search?type=business']],
            [HomepageSectionType::LatestListings, ['en' => 'Property advertisements', 'km' => 'ការផ្សាយអចលនទ្រព្យ'], ['en' => 'Homes, apartments, and commercial spaces for rent or sale.', 'km' => null], ['item_count' => 4, 'button_label' => ['en' => 'All properties', 'km' => null], 'link_url' => '/search?type=property']],
            [HomepageSectionType::SocialHighlights, ['en' => 'Social highlights', 'km' => 'ចំណុចលេចធ្លោក្នុងសហគមន៍'], ['en' => 'What people are sharing on Cambo Hub.', 'km' => null], ['item_count' => 3, 'button_label' => ['en' => 'Community', 'km' => null], 'link_url' => '/search?type=social-post']],
            [HomepageSectionType::UpcomingEvents, ['en' => 'Featured events', 'km' => 'ព្រឹត្តិការណ៍ពិសេស'], ['en' => 'Workshops, festivals, and meetups coming up soon.', 'km' => null], ['item_count' => 4, 'button_label' => ['en' => 'All events', 'km' => null], 'link_url' => '/search?type=event']],
            [HomepageSectionType::PopularCourses, ['en' => 'Popular courses', 'km' => 'វគ្គសិក្សាពេញនិយម'], null, ['item_count' => 4, 'button_label' => ['en' => 'All courses', 'km' => null], 'link_url' => '/search?type=course']],
            [HomepageSectionType::PromoBlock, ['en' => 'Join Cambo Hub today.', 'km' => 'ចូលរួមជាមួយ Cambo Hub ថ្ងៃនេះ។'], ['en' => 'Apply for jobs, save products, follow businesses, and book events with a single secure account.', 'km' => null], ['button_label' => ['en' => 'Create your account', 'km' => null], 'link_url' => '/register']],
        ];

        foreach ($sections as $position => [$type, $title, $subtitle, $extra]) {
            HomepageSection::create([
                'type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'item_count' => 6,
                'is_visible' => true,
                'sort_order' => $position,
                ...$extra,
            ]);
        }
    }

    /**
     * @param  array<string, Page>  $pages
     */
    private function seedMenus(array $pages): void
    {
        if (! MenuItem::where('location', MenuLocation::Header)->exists()) {
            // Module pages are not built yet, so service links open search filtered to that service.
            $headerLinks = [
                [['en' => 'Home', 'km' => 'ទំព័រដើម'], '/'],
                [['en' => 'News', 'km' => 'ព័ត៌មាន'], '/search?type=news'],
                [['en' => 'Jobs', 'km' => 'ការងារ'], '/search?type=job'],
                [['en' => 'Marketplace', 'km' => 'ទីផ្សារ'], '/search?type=product'],
                [['en' => 'Businesses', 'km' => 'អាជីវកម្ម'], '/search?type=business'],
                [['en' => 'Events', 'km' => 'ព្រឹត្តិការណ៍'], '/search?type=event'],
            ];

            foreach ($headerLinks as $position => [$label, $url]) {
                MenuItem::create(['location' => MenuLocation::Header, 'label' => $label, 'url' => $url, 'sort_order' => $position]);
            }

            $about = MenuItem::create(['location' => MenuLocation::Header, 'label' => ['en' => 'About', 'km' => 'អំពី'], 'url' => '#', 'sort_order' => count($headerLinks)]);
            $aboutLinks = [
                [$pages['about-us']->title, null, $pages['about-us']->id],
                [['en' => 'Our mission', 'km' => 'បេសកកម្មរបស់យើង'], '/mission', null],
                [['en' => 'Our vision', 'km' => 'ចក្ខុវិស័យរបស់យើង'], '/vision', null],
                [['en' => 'Contact', 'km' => 'ទំនាក់ទំនង'], '/contact', null],
            ];

            foreach ($aboutLinks as $position => [$label, $url, $pageId]) {
                MenuItem::create(['location' => MenuLocation::Header, 'parent_id' => $about->id, 'label' => $label, 'url' => $url, 'page_id' => $pageId, 'sort_order' => $position]);
            }
        }

        if (! MenuItem::where('location', MenuLocation::Footer)->exists()) {
            $company = MenuItem::create(['location' => MenuLocation::Footer, 'label' => ['en' => 'Company', 'km' => 'ក្រុមហ៊ុន'], 'url' => '#', 'sort_order' => 0]);
            $legal = MenuItem::create(['location' => MenuLocation::Footer, 'label' => ['en' => 'Legal', 'km' => 'ច្បាប់'], 'url' => '#', 'sort_order' => 1]);

            $footerLinks = [
                [$company, $pages['about-us']->title, null, $pages['about-us']->id],
                [$company, ['en' => 'Our mission', 'km' => 'បេសកកម្មរបស់យើង'], '/mission', null],
                [$company, ['en' => 'Our vision', 'km' => 'ចក្ខុវិស័យរបស់យើង'], '/vision', null],
                [$company, ['en' => 'Contact', 'km' => 'ទំនាក់ទំនង'], '/contact', null],
                [$legal, $pages['terms']->title, null, $pages['terms']->id],
                [$legal, $pages['privacy']->title, null, $pages['privacy']->id],
            ];

            foreach ($footerLinks as $position => [$parent, $label, $url, $pageId]) {
                MenuItem::create([
                    'location' => MenuLocation::Footer,
                    'parent_id' => $parent->id,
                    'label' => $label,
                    'url' => $url,
                    'page_id' => $pageId,
                    'sort_order' => $position,
                ]);
            }
        }
    }
}
