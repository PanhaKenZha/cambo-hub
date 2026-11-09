<?php

namespace Database\Seeders;

use App\Enums\BannerPlacement;
use App\Enums\CmsContentType;
use App\Models\Banner;
use App\Models\CmsContent;
use App\Services\SiteContent;
use Illuminate\Database\Seeder;

/**
 * Starter items for the homepage sections and search, plus homepage promotional banners.
 * Only fills empty areas, so content administrators have added is never touched.
 */
class ContentItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBanners();

        if (! CmsContent::query()->exists()) {
            foreach ($this->starterContent() as $contentType => $items) {
                foreach (array_values($items) as $index => $item) {
                    CmsContent::create([
                        'content_type' => $contentType,
                        'published_at' => now()->subHours($index * 5 + 1),
                        'sort_order' => $index,
                        ...$item,
                    ]);
                }
            }
        }

        app(SiteContent::class)->flush();
    }

    private function seedBanners(): void
    {
        if (Banner::where('placement', BannerPlacement::HomepageHero)->exists()) {
            return;
        }

        $banners = [
            ['title' => ['en' => 'Hire faster with Cambo Hub Jobs', 'km' => null], 'subtitle' => ['en' => 'Post your first job listing free this month and reach thousands of candidates.', 'km' => null], 'button_label' => ['en' => 'Start hiring', 'km' => null], 'image_path' => 'images/backgrounds/jobs.webp'],
            ['title' => ['en' => 'Sell to all of Cambodia', 'km' => null], 'subtitle' => ['en' => 'Open your online shop in minutes with secure payments and delivery partners.', 'km' => null], 'button_label' => ['en' => 'Open a shop', 'km' => null], 'image_path' => 'images/backgrounds/marketplace.webp'],
        ];

        foreach ($banners as $position => $banner) {
            Banner::create([...$banner, 'placement' => BannerPlacement::HomepageHero, 'link_url' => '/register', 'is_active' => true, 'sort_order' => $position]);
        }
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function starterContent(): array
    {
        $search = fn (CmsContentType $type): string => '/search?type='.$type->value;

        return [
            CmsContentType::ServiceCategory->value => [
                ['title' => 'News', 'summary' => 'Local and world headlines', 'link_url' => $search(CmsContentType::News), 'meta' => ['icon' => 'news', 'module' => 'news']],
                ['title' => 'Jobs', 'summary' => 'Find your next role', 'link_url' => $search(CmsContentType::Job), 'meta' => ['icon' => 'jobs', 'module' => 'jobs']],
                ['title' => 'Marketplace', 'summary' => 'Shop local sellers', 'link_url' => $search(CmsContentType::Product), 'meta' => ['icon' => 'shopping', 'module' => 'marketplace']],
                ['title' => 'Businesses', 'summary' => 'Discover trusted companies', 'link_url' => $search(CmsContentType::Business), 'meta' => ['icon' => 'business', 'module' => 'business']],
                ['title' => 'Property', 'summary' => 'Homes and spaces', 'link_url' => $search(CmsContentType::Property), 'meta' => ['icon' => 'property', 'module' => 'listings']],
                ['title' => 'Vehicles', 'summary' => 'Cars and motorbikes', 'link_url' => null, 'meta' => ['icon' => 'vehicles', 'module' => 'listings']],
                ['title' => 'Community', 'summary' => 'Share and connect', 'link_url' => $search(CmsContentType::SocialPost), 'meta' => ['icon' => 'social', 'module' => 'social']],
                ['title' => 'Events', 'summary' => 'What’s on near you', 'link_url' => $search(CmsContentType::Event), 'meta' => ['icon' => 'events', 'module' => 'events']],
                ['title' => 'Education', 'summary' => 'Courses and skills', 'link_url' => $search(CmsContentType::Course), 'meta' => ['icon' => 'education', 'module' => 'education']],
                ['title' => 'Freelance', 'summary' => 'Hire or get hired', 'link_url' => null, 'meta' => ['icon' => 'freelance', 'module' => 'jobs']],
                ['title' => 'Hosting', 'summary' => 'Domains and websites', 'link_url' => null, 'meta' => ['icon' => 'hosting', 'module' => 'hosting']],
                ['title' => 'Support', 'summary' => 'We’re here to help', 'link_url' => '/contact', 'meta' => ['icon' => 'support']],
            ],
            CmsContentType::Story->value => [
                ['title' => 'Cambo Hub', 'badge' => 'Official', 'image_path' => 'images/branding/cambo-hub-mark.svg'],
                ['title' => 'Phnom Penh Eats', 'badge' => '2h', 'image_path' => 'images/content/marketplace.webp'],
                ['title' => 'Career Corner', 'badge' => '4h', 'image_path' => 'images/content/jobs-recruitment.webp'],
                ['title' => 'Siem Reap Today', 'badge' => '6h', 'image_path' => 'images/backgrounds/cambodia-platform.webp'],
                ['title' => 'Tech Khmer', 'badge' => '8h', 'image_path' => 'images/backgrounds/ai-assistant.webp'],
                ['title' => 'Learn Daily', 'badge' => '10h', 'image_path' => 'images/backgrounds/education.webp'],
                ['title' => 'Home Finder', 'badge' => '12h', 'image_path' => 'images/backgrounds/real-estate.webp'],
            ],
            CmsContentType::News->value => [
                ['title' => 'Phnom Penh launches new smart bus routes connecting the city centre', 'summary' => 'Commuters can now track buses in real time as the capital expands its public transport network.', 'badge' => 'City', 'image_path' => 'images/backgrounds/transport.webp', 'is_featured' => true],
                ['title' => 'Digital payments continue rapid growth across Cambodian provinces', 'badge' => 'Economy', 'image_path' => 'images/backgrounds/payments.webp'],
                ['title' => 'Young entrepreneurs showcase local brands at Koh Pich fair', 'badge' => 'Business', 'image_path' => 'images/backgrounds/business-directory.webp'],
                ['title' => 'Free online coding courses open for Khmer-speaking students', 'badge' => 'Education', 'image_path' => 'images/backgrounds/education.webp'],
            ],
            CmsContentType::Job->value => [
                ['title' => 'Senior Accountant', 'summary' => 'Lead monthly closing and reporting for a growing retail group.', 'badge' => 'Urgent', 'is_featured' => true, 'meta' => ['company' => 'Mekong Retail Group', 'location' => 'Phnom Penh', 'employment_type' => 'Full-time', 'salary' => '$900 – $1,300']],
                ['title' => 'Frontend Developer', 'summary' => 'Build modern web interfaces with a product team.', 'meta' => ['company' => 'Angkor Digital', 'location' => 'Phnom Penh · Hybrid', 'employment_type' => 'Full-time', 'salary' => '$1,200 – $1,800']],
                ['title' => 'Hotel Front Office Supervisor', 'summary' => 'Welcome guests and lead the reception team.', 'meta' => ['company' => 'Temple View Resort', 'location' => 'Siem Reap', 'employment_type' => 'Full-time', 'salary' => '$600 – $800']],
                ['title' => 'Marketing Executive', 'summary' => 'Plan campaigns across social and digital channels.', 'meta' => ['company' => 'Khmer Coffee Co.', 'location' => 'Phnom Penh', 'employment_type' => 'Full-time', 'salary' => '$550 – $750']],
                ['title' => 'Logistics Coordinator', 'summary' => 'Coordinate shipments between port and warehouses.', 'meta' => ['company' => 'Sihanoukville Port Logistics', 'location' => 'Sihanoukville', 'employment_type' => 'Contract', 'salary' => '$700 – $950']],
                ['title' => 'English Teacher', 'summary' => 'Teach young learners in a friendly language school.', 'meta' => ['company' => 'Bright Future School', 'location' => 'Battambang', 'employment_type' => 'Part-time', 'salary' => '$12 / hour']],
            ],
            CmsContentType::Product->value => [
                ['title' => 'Mondulkiri Arabica Coffee 500g', 'summary' => 'Single-origin beans roasted in Phnom Penh.', 'badge' => 'Best seller', 'image_path' => 'images/content/marketplace.webp', 'meta' => ['seller' => 'Khmer Coffee Co.', 'price' => '$8.50', 'rating' => '4.9']],
                ['title' => 'Handwoven Silk Krama Scarf', 'summary' => 'Traditional Cambodian silk krama, handwoven in Takeo.', 'meta' => ['seller' => 'Takeo Weavers', 'price' => '$15.00', 'rating' => '4.8']],
                ['title' => 'Smartphone 128GB – Midnight Blue', 'summary' => 'Unlocked smartphone with 1-year local warranty.', 'badge' => '-15%', 'meta' => ['seller' => 'Phnom Penh Mobile', 'price' => '$229.00', 'rating' => '4.6']],
                ['title' => 'Kampot Pepper Gift Set', 'summary' => 'Black, red, and white Kampot pepper in a gift box.', 'meta' => ['seller' => 'Kampot Farm Shop', 'price' => '$12.00', 'rating' => '4.9']],
            ],
            CmsContentType::Advertisement->value => [
                ['title' => 'Send money home instantly', 'summary' => 'Low-fee transfers to any bank in Cambodia, 24/7.', 'image_path' => 'images/backgrounds/payments.webp', 'link_url' => '/contact', 'meta' => ['advertiser' => 'Partner Bank', 'cta_label' => 'Learn more']],
                ['title' => 'Get your business online', 'summary' => 'Domains, hosting, and email from $2.99 per month.', 'image_path' => 'images/backgrounds/hosting.webp', 'link_url' => '/contact', 'meta' => ['advertiser' => 'Cambo Hub Hosting', 'cta_label' => 'See plans']],
            ],
            CmsContentType::Business->value => [
                ['title' => 'Khmer Coffee Co.', 'summary' => 'Specialty coffee roaster and café chain serving Cambodian beans.', 'badge' => 'Food & drink', 'meta' => ['location' => 'BKK1, Phnom Penh', 'rating' => '4.8']],
                ['title' => 'Angkor Digital', 'summary' => 'Web, mobile, and cloud solutions for Cambodian businesses.', 'badge' => 'Technology', 'meta' => ['location' => 'Toul Kork, Phnom Penh', 'rating' => '4.7']],
                ['title' => 'Temple View Resort', 'summary' => 'Boutique resort minutes from Angkor Wat.', 'badge' => 'Hospitality', 'meta' => ['location' => 'Siem Reap', 'rating' => '4.9']],
            ],
            CmsContentType::Property->value => [
                ['title' => 'Modern 2-bedroom condo with city view', 'summary' => 'Fully furnished condo with pool and gym.', 'badge' => 'For rent', 'image_path' => 'images/backgrounds/real-estate.webp', 'meta' => ['price' => '$650 / month', 'location' => 'BKK1, Phnom Penh', 'bedrooms' => 2, 'bathrooms' => 2, 'area' => '86 m²']],
                ['title' => 'Family villa near Riverside', 'summary' => 'Spacious villa with garden and parking.', 'badge' => 'For sale', 'image_path' => 'images/backgrounds/business-directory.webp', 'meta' => ['price' => '$285,000', 'location' => 'Chroy Changvar, Phnom Penh', 'bedrooms' => 4, 'bathrooms' => 3, 'area' => '320 m²']],
                ['title' => 'Shophouse on main road', 'summary' => 'Ground-floor commercial space with living area above.', 'badge' => 'For rent', 'image_path' => 'images/backgrounds/marketplace.webp', 'meta' => ['price' => '$1,200 / month', 'location' => 'Sen Sok, Phnom Penh', 'bathrooms' => 2, 'area' => '4 × 16 m']],
                ['title' => 'Studio apartment near Pub Street', 'summary' => 'Cozy studio, walking distance to the old market.', 'badge' => 'For rent', 'image_path' => 'images/backgrounds/cambodia-platform.webp', 'meta' => ['price' => '$280 / month', 'location' => 'Siem Reap', 'bedrooms' => 1, 'bathrooms' => 1, 'area' => '38 m²']],
            ],
            CmsContentType::SocialPost->value => [
                ['title' => 'First week at my new job', 'summary' => 'Started my first week as a junior developer after finding the role on Cambo Hub. Thank you to everyone who shared tips! 🎉', 'meta' => ['author' => 'Sokha Chan', 'likes' => 248, 'comments' => 36]],
                ['title' => 'Sunrise at Angkor', 'summary' => 'Sunrise at Angkor Wat never gets old. Who else is visiting Siem Reap this weekend?', 'image_path' => 'images/backgrounds/cambodia-platform.webp', 'meta' => ['author' => 'Dara Pich', 'likes' => 512, 'comments' => 58]],
                ['title' => 'Supporting local makers', 'summary' => 'We just restocked our handwoven krama collection. Every purchase supports weavers in Takeo province.', 'meta' => ['author' => 'Takeo Weavers', 'likes' => 131, 'comments' => 12]],
            ],
            CmsContentType::Event->value => [
                ['title' => 'Cambodia Tech Summit', 'summary' => 'Talks and workshops on AI, fintech, and startups.', 'badge' => 'Conference', 'image_path' => 'images/backgrounds/events.webp', 'is_featured' => true, 'meta' => ['date' => now()->addDays(12)->setTime(9, 0)->toDateTimeString(), 'venue' => 'Diamond Island Convention Center']],
                ['title' => 'Job Fair Phnom Penh', 'summary' => 'Meet more than 80 employers hiring now.', 'badge' => 'Careers', 'image_path' => 'images/content/jobs-recruitment.webp', 'meta' => ['date' => now()->addDays(20)->setTime(8, 30)->toDateTimeString(), 'venue' => 'Koh Pich Exhibition Hall']],
                ['title' => 'Water Festival Night Market', 'summary' => 'Food, crafts, and live music along the riverside.', 'badge' => 'Festival', 'image_path' => 'images/content/marketplace.webp', 'meta' => ['date' => now()->addDays(5)->setTime(17, 0)->toDateTimeString(), 'venue' => 'Sisowath Quay']],
                ['title' => 'Small Business Digital Workshop', 'summary' => 'Learn to sell online and accept digital payments.', 'badge' => 'Workshop', 'image_path' => 'images/backgrounds/analytics.webp', 'meta' => ['date' => now()->addDays(9)->setTime(14, 0)->toDateTimeString(), 'venue' => 'Cambo Hub Space, Toul Kork']],
            ],
            CmsContentType::Course->value => [
                ['title' => 'Web Development for Beginners', 'summary' => 'Build your first website with HTML, CSS and JavaScript.', 'badge' => 'Beginner', 'image_path' => 'images/backgrounds/education.webp', 'is_featured' => true, 'meta' => ['provider' => 'Angkor Digital Academy', 'price' => 'Free', 'duration' => '6 weeks', 'rating' => '4.8']],
                ['title' => 'Practical Accounting with Excel', 'summary' => 'Bookkeeping, payroll and reports for small businesses.', 'badge' => 'Popular', 'image_path' => 'images/backgrounds/analytics.webp', 'meta' => ['provider' => 'Mekong Business School', 'price' => '$49', 'duration' => '4 weeks', 'rating' => '4.7']],
                ['title' => 'Business English Essentials', 'summary' => 'Emails, meetings and presentations with confidence.', 'image_path' => 'images/content/jobs-recruitment.webp', 'meta' => ['provider' => 'Bright Future School', 'price' => '$29', 'duration' => '8 weeks', 'rating' => '4.9']],
                ['title' => 'Digital Marketing for Local Shops', 'summary' => 'Grow sales with social media, ads and online stores.', 'image_path' => 'images/content/marketplace.webp', 'meta' => ['provider' => 'Cambo Hub Learning', 'price' => '$39', 'duration' => '3 weeks', 'rating' => '4.6']],
            ],
        ];
    }
}
