<?php

namespace App\Models;

use App\Enums\CmsContentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * CMS controlled placement, ordering, and copy for one homepage section.
 */
#[Fillable(['content_type', 'eyebrow', 'heading', 'subheading', 'link_label', 'link_url', 'item_limit', 'sort_order', 'is_enabled'])]
class HomepageSection extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content_type' => CmsContentType::class,
            'item_limit' => 'integer',
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }
}
