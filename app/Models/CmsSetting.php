<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A named block of site-wide configuration managed through the CMS,
 * such as branding, navigation, hero copy, or footer links.
 */
#[Fillable(['key', 'value'])]
class CmsSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
