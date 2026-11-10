<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ApprovalStatus;
use App\Enums\ArticleStatus;
use App\Enums\CmsContentType;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Middleware\EnsureAdminSession;
use App\Models\ApprovalRequest;
use App\Models\CmsContent;
use App\Models\NewsArticle;
use App\Models\NewsCategory;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ContentItems;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
    }

    public function test_editor_publishes_article_and_it_appears_on_homepage_and_search(): void
    {
        Storage::fake('public');
        $editor = $this->signInAdmin(AdminRole::NewsAdmin);
        $category = NewsCategory::factory()->create(['name' => ['en' => 'Economy', 'km' => 'សេដ្ឋកិច្ច']]);

        $this->get('/admin/news/articles/create')->assertOk()->assertSee('Article text');
        $response = $this->post('/admin/news/articles', [
            'title' => ['en' => 'Riel stays stable against the dollar', 'km' => 'ប្រាក់រៀលនៅតែមានស្ថិរភាព'],
            'excerpt' => ['en' => 'The exchange rate held steady this quarter.'],
            'body' => ['en' => "## Steady rate\n\nThe riel held at about **4,100** per dollar."],
            'news_category_id' => $category->id,
            'status' => 'published',
            'is_featured' => '1',
            'allow_comments' => '1',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
        ]);

        $article = NewsArticle::where('slug', 'riel-stays-stable-against-the-dollar')->firstOrFail();
        $response->assertRedirect("/admin/news/articles/{$article->id}/edit");
        $this->assertTrue($article->isLive());
        $this->assertSame($editor->id, $article->created_by);
        Storage::disk('public')->assertExists($article->cover_image_path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'news.article.created', 'auditable_id' => $article->id]);

        $item = CmsContent::where('source_type', 'news_article')->where('source_id', $article->id)->firstOrFail();
        $this->assertSame(CmsContentType::News, $item->content_type);
        $this->assertSame('Economy', $item->badge);
        $this->assertSame('/news/riel-stays-stable-against-the-dollar', $item->link_url);
        $this->assertTrue($item->is_featured);
        $this->assertSame('Riel stays stable against the dollar', app(ContentItems::class)->latest(CmsContentType::News, 5)->first()->title);
        $this->assertNotEmpty(app(ContentItems::class)->search('riel'));
    }

    public function test_drafts_are_not_public_and_unpublishing_removes_the_homepage_item(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $draft = NewsArticle::factory()->draft()->create(['slug' => 'secret-draft']);
        $live = NewsArticle::factory()->create(['slug' => 'live-story']);

        $this->assertDatabaseMissing('cms_contents', ['source_id' => $draft->id, 'source_type' => 'news_article']);
        $this->get('/news/secret-draft')->assertNotFound();
        $this->get('/news/live-story')->assertOk();

        $this->put("/admin/news/articles/{$live->id}", [
            'title' => $live->title, 'body' => $live->body, 'slug' => 'live-story', 'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseMissing('cms_contents', ['source_id' => $live->id, 'source_type' => 'news_article']);
        $this->get('/news/live-story')->assertNotFound();
    }

    public function test_scheduled_articles_go_live_on_their_publish_date(): void
    {
        NewsArticle::factory()->create(['slug' => 'tomorrow', 'title' => ['en' => 'Tomorrow story'], 'published_at' => now()->addDay()]);

        $this->get('/news/tomorrow')->assertNotFound();
        $this->assertTrue(app(ContentItems::class)->latest(CmsContentType::News, 5)->isEmpty());

        $this->travel(2)->days();

        $this->get('/news/tomorrow')->assertOk()->assertSee('Tomorrow story');
        $this->assertSame('Tomorrow story', app(ContentItems::class)->latest(CmsContentType::News, 5)->first()->title);
    }

    public function test_writer_without_publish_rights_sends_article_for_review_and_approval_publishes_it(): void
    {
        $writer = $this->signInAdmin(AdminRole::SupportAgent);
        $writer->givePermissionTo([
            PermissionArea::News->permission(PermissionAction::View),
            PermissionArea::News->permission(PermissionAction::Create),
            PermissionArea::News->permission(PermissionAction::Edit),
        ]);

        $this->post('/admin/news/articles', [
            'title' => ['en' => 'Reader tips for the water festival'],
            'body' => ['en' => 'Plan your trip early.'],
            'status' => 'published',
            'is_featured' => '1',
            'intent' => 'submit',
        ])->assertRedirect();

        $article = NewsArticle::firstOrFail();
        $this->assertSame(ArticleStatus::InReview, $article->status);
        $this->assertFalse($article->is_featured);
        $this->assertSame($writer->id, $article->submitted_by);
        $this->assertDatabaseMissing('cms_contents', ['source_id' => $article->id, 'source_type' => 'news_article']);

        $review = ApprovalRequest::firstOrFail();
        $this->assertSame('Reader tips for the water festival', $review->title);

        $reviewer = User::factory()->admin(AdminRole::NewsAdmin)->create();
        app(ApprovalService::class)->approve($review, $reviewer);

        $this->assertTrue($article->fresh()->isLive());
        $this->assertDatabaseHas('cms_contents', ['source_id' => $article->id, 'source_type' => 'news_article']);
    }

    public function test_rejected_article_returns_to_writer_and_can_be_resubmitted(): void
    {
        $writer = $this->signInAdmin(AdminRole::SupportAgent);
        $writer->givePermissionTo([PermissionArea::News->permission(PermissionAction::View), PermissionArea::News->permission(PermissionAction::Edit)]);
        $article = NewsArticle::factory()->draft()->create();

        $this->post("/admin/news/articles/{$article->id}/submit")->assertRedirect();
        app(ApprovalService::class)->reject(ApprovalRequest::firstOrFail(), User::factory()->admin(AdminRole::NewsAdmin)->create(), 'Please add a source.');

        $this->assertSame(ArticleStatus::Rejected, $article->fresh()->status);
        $this->get("/admin/news/articles/{$article->id}/edit")->assertOk()->assertSee('Please add a source.');

        $this->post("/admin/news/articles/{$article->id}/submit")->assertRedirect();
        $this->assertSame(ArticleStatus::InReview, $article->fresh()->status);
        $this->assertSame(2, ApprovalRequest::count());
    }

    public function test_editor_publishing_from_the_editor_closes_the_open_review(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $article = NewsArticle::factory()->draft()->create(['slug' => 'pending-story']);
        $article->update(['status' => ArticleStatus::InReview]);
        $review = app(ApprovalService::class)->submit($article, User::factory()->create());

        $this->put("/admin/news/articles/{$article->id}", ['title' => $article->title, 'body' => $article->body, 'slug' => 'pending-story', 'status' => 'published']);

        $this->assertSame(ApprovalStatus::Approved, $review->fresh()->status);
        $this->assertTrue($article->fresh()->isLive());
    }

    public function test_list_tabs_feature_toggle_and_permissions(): void
    {
        $this->signInAdmin(AdminRole::NewsAdmin);
        $article = NewsArticle::factory()->create(['title' => ['en' => 'Featured candidate']]);
        NewsArticle::factory()->draft()->create(['title' => ['en' => 'Unfinished draft']]);

        $this->get('/admin/news/articles?tab=draft')->assertOk()->assertSee('Unfinished draft')->assertDontSee('Featured candidate');
        $this->post("/admin/news/articles/{$article->id}/feature")->assertRedirect();
        $this->assertTrue($article->fresh()->is_featured);
        $this->assertTrue(CmsContent::where('source_id', $article->id)->value('is_featured'));
        $this->get('/admin/news/articles?tab=featured')->assertSee('Featured candidate');

        $this->signInAdmin(AdminRole::JobsAdmin);
        $this->get('/admin/news/articles')->assertForbidden();
    }

    public function test_deleting_article_removes_it_everywhere(): void
    {
        $this->signInAdmin(AdminRole::SuperAdmin);
        $article = NewsArticle::factory()->create();

        $this->delete("/admin/news/articles/{$article->id}")->assertRedirect('/admin/news/articles');

        $this->assertDatabaseMissing('news_articles', ['id' => $article->id]);
        $this->assertDatabaseMissing('cms_contents', ['source_id' => $article->id, 'source_type' => 'news_article']);
    }

    private function signInAdmin(AdminRole $role): User
    {
        $admin = User::factory()->withTwoFactor()->admin($role)->create();
        $this->actingAs($admin)->withSession([EnsureAdminSession::VERIFIED_SESSION_KEY => true]);

        return $admin;
    }
}
