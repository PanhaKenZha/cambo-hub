<?php

namespace App\Http\Controllers\Admin\Cms\Concerns;

use App\Services\PlatformSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Image uploads for CMS content, limited by Admin › Platform settings › Uploads.
 */
trait HandlesImageUploads
{
    /**
     * @return list<string>
     */
    protected function imageRules(bool $required): array
    {
        $settings = app(PlatformSettings::class);
        $allowedTypes = array_values(array_diff($settings->get('uploads.image_types', ['jpg', 'jpeg', 'png', 'webp']), ['svg']));

        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', $allowedTypes),
            'max:'.((int) $settings->get('uploads.max_size_mb', 10) * 1024),
        ];
    }

    protected function storeImage(UploadedFile $file, string $folder, ?string $replacing = null): string
    {
        $this->deleteImage($replacing);

        return $file->store("cms/{$folder}", 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }
    }
}
