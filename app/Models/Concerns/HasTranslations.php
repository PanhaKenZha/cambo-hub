<?php

namespace App\Models\Concerns;

use App\Services\PlatformSettings;

/**
 * Columns cast to "array" that hold one value per language, e.g. {"en": "About", "km": "អំពីយើង"}.
 */
trait HasTranslations
{
    /**
     * Text in the requested language, falling back to the default language and then any language.
     */
    public function translate(string $attribute, ?string $locale = null): string
    {
        $values = array_filter((array) ($this->getAttribute($attribute) ?? []), fn ($value): bool => filled($value));
        $locale ??= app()->getLocale();
        $defaultLocale = app(PlatformSettings::class)->get('localization.default_locale', 'en');

        return (string) ($values[$locale] ?? $values[$defaultLocale] ?? $values['en'] ?? reset($values) ?: '');
    }

    /**
     * Turn submitted form input into one entry per language, with blanks stored as null.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, ?string>
     */
    public static function normalizeTranslations(array $values): array
    {
        return collect(static::translationLocales())
            ->mapWithKeys(fn (string $label, string $locale): array => [$locale => filled($values[$locale] ?? null) ? trim((string) $values[$locale]) : null])
            ->all();
    }

    /**
     * Languages offered in the admin forms.
     *
     * @return array<string, string>
     */
    public static function translationLocales(): array
    {
        return ['en' => 'English', 'km' => 'ខ្មែរ'];
    }
}
