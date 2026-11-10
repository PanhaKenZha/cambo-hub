<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\CmsContentType;
use App\Models\CmsContent;
use App\Models\NewsArticle;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use App\Services\NewsContentSync;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Starter news: categories, a newsroom author and a few articles.
 *
 * The homepage's demo news items are replaced by real articles, which then appear
 * on the homepage and in search through the automatic sync. Only fills empty areas.
 */
class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        if (NewsArticle::query()->exists()) {
            return;
        }

        $author = NewsAuthor::firstOrCreate(['slug' => 'cambo-hub-newsroom'], [
            'name' => 'Cambo Hub Newsroom',
            'bio' => ['en' => 'News from the Cambo Hub editorial team.', 'km' => 'ព័ត៌មានពីក្រុមវិចារណកថា Cambo Hub'],
        ]);

        // Demo homepage news items that were not generated from an article.
        CmsContent::where('content_type', CmsContentType::News)->whereNull('source_type')->delete();

        $sync = app(NewsContentSync::class);

        foreach ($this->starterArticles() as $index => $item) {
            $article = NewsArticle::create([
                'news_category_id' => $categories[$item['category']]->id,
                'news_author_id' => $author->id,
                'slug' => Str::slug($item['title']),
                'title' => ['en' => $item['title'], 'km' => null],
                'excerpt' => ['en' => $item['excerpt'], 'km' => null],
                'body' => ['en' => $item['body'], 'km' => null],
                'cover_image_path' => $this->copyCoverImage($item['image']),
                'status' => ArticleStatus::Published,
                'published_at' => now()->subHours($index * 5 + 1),
                'is_featured' => $index === 0,
                'allow_comments' => true,
            ]);

            // DatabaseSeeder disables model events, so sync explicitly.
            $sync->sync($article);
        }
    }

    /**
     * Copy a bundled photo into public storage so it can be used as an uploaded cover image.
     */
    public function copyCoverImage(string $bundledImage): ?string
    {
        $source = public_path($bundledImage);

        if (! is_file($source)) {
            return null;
        }

        $path = 'cms/news/'.basename($bundledImage);
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }

    /**
     * @return array<string, NewsCategory>
     */
    private function seedCategories(): array
    {
        $categories = [
            'city' => ['en' => 'City', 'km' => 'ទីក្រុង'],
            'economy' => ['en' => 'Economy', 'km' => 'សេដ្ឋកិច្ច'],
            'business' => ['en' => 'Business', 'km' => 'អាជីវកម្ម'],
            'education' => ['en' => 'Education', 'km' => 'អប់រំ'],
            'technology' => ['en' => 'Technology', 'km' => 'បច្ចេកវិទ្យា'],
            'sports' => ['en' => 'Sports', 'km' => 'កីឡា'],
            'world' => ['en' => 'World', 'km' => 'ពិភពលោក'],
        ];

        $position = 0;

        return collect($categories)->mapWithKeys(fn (array $name, string $slug): array => [
            $slug => NewsCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'sort_order' => $position++]),
        ])->all();
    }

    /**
     * @return list<array{title: string, excerpt: string, body: string, category: string, image: string}>
     */
    private function starterArticles(): array
    {
        return [
            [
                'title' => 'Phnom Penh launches new smart bus routes connecting the city centre',
                'excerpt' => 'Commuters can now track buses in real time as the capital expands its public transport network.',
                'body' => "Phnom Penh has opened new smart bus routes linking the city centre with growing districts.\n\n## Real-time tracking\n\nPassengers can follow buses live on their phones and plan journeys with fewer delays.",
                'category' => 'city',
                'image' => 'images/backgrounds/transport.webp',
            ],
            [
                'title' => 'Digital payments continue rapid growth across Cambodian provinces',
                'excerpt' => 'QR payments are now common in markets and small shops outside the capital.',
                'body' => "Digital payments keep growing across Cambodia's provinces.\n\nMarket vendors and small shops increasingly accept KHQR, making everyday purchases faster.",
                'category' => 'economy',
                'image' => 'images/backgrounds/payments.webp',
            ],
            [
                'title' => 'Young entrepreneurs showcase local brands at Koh Pich fair',
                'excerpt' => 'More than a hundred Cambodian start-ups met customers and investors over the weekend.',
                'body' => "Young founders presented local food, fashion and technology brands at a weekend fair on Koh Pich.\n\nOrganisers say the event connected start-ups with new customers and investors.",
                'category' => 'business',
                'image' => 'images/backgrounds/business-directory.webp',
            ],
            [
                'title' => 'Free online coding courses open for Khmer-speaking students',
                'excerpt' => 'New beginner courses teach web development in Khmer, with certificates on completion.',
                'body' => "A new set of free online coding courses is now available in Khmer.\n\n- Beginner web development\n- Introduction to databases\n- Building your first app",
                'category' => 'education',
                'image' => 'images/backgrounds/education.webp',
            ],
        ];
    }
}
