<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\CommentStatus;
use App\Enums\PlatformModule;
use App\Enums\ReportReason;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\CmsContent;
use App\Models\NewsArticle;
use App\Models\NewsAuthor;
use App\Models\NewsCategory;
use App\Models\NewsComment;
use App\Models\User;
use App\Services\ModerationService;
use App\Services\PlatformSettings;
use Database\Seeders\ContentItemSeeder;
use Database\Seeders\NewsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsCatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_categories_can_be_managed_and_renaming_updates_homepage_badges(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);

        $this->post('/admin/news/categories', ['name' => ['en' => 'Health', 'km' => 'សុខភាព'], 'is_active' => '1'])->assertRedirect('/admin/news/categories');
        $category = NewsCategory::where('slug', 'health')->firstOrFail();
        $article = NewsArticle::factory()->create(['news_category_id' => $category->id]);

        $this->put("/admin/news/categories/{$category->id}", ['name' => ['en' => 'Health & wellbeing'], 'slug' => 'health', 'is_active' => '1'])->assertRedirect();

        $this->assertSame('Health & wellbeing', CmsContent::where('source_id', $article->id)->value('badge'));
        $this->get('/admin/news/categories')->assertOk()->assertSee('Health &amp; wellbeing', false);

        $this->delete("/admin/news/categories/{$category->id}")->assertRedirect();
        $this->assertNull($article->fresh()->news_category_id);
    }

    public function test_authors_can_be_linked_to_an_account(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $writer = User::factory()->create(['email' => 'writer@example.com']);

        $this->post('/admin/news/authors', ['name' => 'Sokha Chan', 'user_email' => 'writer@example.com', 'is_active' => '1'])->assertRedirect('/admin/news/authors');
        $this->post('/admin/news/authors', ['name' => 'Duplicate', 'user_email' => 'writer@example.com'])->assertSessionHasErrors('user_email');
        $this->post('/admin/news/authors', ['name' => 'Ghost', 'user_email' => 'nobody@example.com'])->assertSessionHasErrors('user_email');

        $this->assertSame($writer->id, NewsAuthor::where('name', 'Sokha Chan')->value('user_id'));
        $this->get('/admin/news/authors')->assertOk()->assertSee('writer@example.com');
    }

    public function test_reader_comments_wait_for_moderation(): void
    {
        $article = NewsArticle::factory()->create(['slug' => 'talk-about-it']);
        $reader = User::factory()->create();

        $this->post('/news/talk-about-it/comments', ['body' => 'Anonymous?'])->assertRedirect('/login');
        $this->actingAs($reader)->post('/news/talk-about-it/comments', ['body' => 'Great reporting, thank you!'])->assertSessionHas('status');

        $comment = NewsComment::firstOrFail();
        $this->assertSame(CommentStatus::Pending, $comment->status);
        $this->get('/news/talk-about-it')->assertDontSee('Great reporting');

        $moderator = $this->signInAdmin(AdminRole::NewsAdmin);
        $this->get('/admin/news/comments')->assertOk()->assertSee('Great reporting');
        $this->post("/admin/news/comments/{$comment->id}", ['action' => 'approve'])->assertRedirect();

        $this->assertSame(CommentStatus::Approved, $comment->fresh()->status);
        $this->assertSame($moderator->id, $comment->fresh()->moderated_by);
        $this->get('/news/talk-about-it')->assertSee('Great reporting');
    }

    public function test_comments_can_be_moderated_in_bulk_and_need_manage_permission(): void
    {
        $comments = NewsComment::factory()->count(3)->create();
        $this->signInAdmin(AdminRole::NewsAdmin);

        $this->post('/admin/news/comments/bulk', ['action' => 'hide', 'ids' => $comments->take(2)->pluck('id')->all()])->assertRedirect();
        $this->assertSame(2, NewsComment::where('status', CommentStatus::Hidden)->count());

        $this->post('/admin/news/comments/bulk', ['action' => 'delete', 'ids' => [$comments->last()->id]]);
        $this->assertSame(2, NewsComment::count());

        $viewer = $this->signInAdmin(AdminRole::PlatformAdmin);
        $viewer->roles->first()->revokePermissionTo('news.manage');
        $this->post('/admin/news/comments/bulk', ['action' => 'approve', 'ids' => $comments->pluck('id')->all()])->assertForbidden();
    }

    public function test_comments_closed_when_article_disallows_them(): void
    {
        NewsArticle::factory()->create(['slug' => 'no-comments', 'allow_comments' => false]);

        $this->actingAs(User::factory()->create())->post('/news/no-comments/comments', ['body' => 'Hello'])->assertForbidden();
    }

    public function test_public_article_renders_safely_and_counts_views(): void
    {
        $article = NewsArticle::factory()->create([
            'slug' => 'safe-story',
            'title' => ['en' => 'Safe story', 'km' => 'រឿងសុវត្ថិភាព'],
            'body' => ['en' => 'Hello **world** <script>alert(1)</script>', 'km' => null],
        ]);

        $this->get('/news/safe-story')->assertOk()->assertSee('<strong>world</strong>', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(1, $article->fresh()->views_count);
    }

    public function test_cover_image_links_use_the_address_the_site_is_opened_on(): void
    {
        NewsArticle::factory()->create(['slug' => 'with-cover', 'cover_image_path' => 'cms/news/cover.jpg']);

        $this->get('http://127.0.0.1:8001/news/with-cover')
            ->assertOk()
            ->assertSee('http://127.0.0.1:8001/storage/cms/news/cover.jpg', false);
    }

    public function test_news_is_hidden_when_module_is_off(): void
    {
        NewsArticle::factory()->create(['slug' => 'any-story']);
        $settings = app(PlatformSettings::class);
        $settings->update('modules', array_merge($settings->group('modules'), [PlatformModule::News->value => false]));

        $this->get('/news/any-story')->assertNotFound();
    }

    public function test_reported_article_can_be_taken_down(): void
    {
        $article = NewsArticle::factory()->create();
        $report = app(ModerationService::class)->report($article, User::factory()->create(), ReportReason::Misinformation);

        app(ModerationService::class)->resolveReport($report, User::factory()->admin(AdminRole::NewsAdmin)->create(), 'False claims', takeDown: true);

        $this->assertFalse($article->fresh()->isLive());
        $this->assertDatabaseMissing('cms_contents', ['source_id' => $article->id, 'source_type' => 'news_article']);
    }

    public function test_seeder_replaces_demo_news_with_real_articles(): void
    {
        Storage::fake('public');
        $this->seed(ContentItemSeeder::class);
        $this->seed(NewsSeeder::class);
        $this->seed(NewsSeeder::class);

        $this->assertSame(4, NewsArticle::count());
        $this->assertSame(7, NewsCategory::count());
        $this->assertSame(4, CmsContent::where('content_type', 'news')->count());
        $this->assertSame(0, CmsContent::where('content_type', 'news')->whereNull('source_type')->count());
        $this->assertSame(4, CmsContent::where('content_type', 'news')->whereNotNull('image_path')->count());
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
