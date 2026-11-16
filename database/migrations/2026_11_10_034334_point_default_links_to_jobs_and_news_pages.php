<?php

use App\Services\SiteContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jobs and News now have their own listing pages. Links that still use the
 * seeded search fallback are moved over; links an administrator changed are left alone.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const LINKS = [
        '/search?type=job' => '/jobs',
        '/search?type=news' => '/news',
    ];

    /**
     * @var array<string, string>
     */
    private const COLUMNS = [
        'menu_items' => 'url',
        'homepage_sections' => 'link_url',
        'cms_contents' => 'link_url',
    ];

    public function up(): void
    {
        $this->swap(self::LINKS);
    }

    public function down(): void
    {
        $this->swap(array_flip(self::LINKS));
    }

    /**
     * @param  array<string, string>  $links
     */
    private function swap(array $links): void
    {
        foreach (self::COLUMNS as $table => $column) {
            foreach ($links as $from => $to) {
                DB::table($table)->where($column, $from)->update([$column => $to]);
            }
        }

        app(SiteContent::class)->flush();
    }
};
