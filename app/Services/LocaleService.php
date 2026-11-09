<?php

namespace App\Services;

use Illuminate\Http\Request;

class LocaleService
{
    /** @return array<string, array{name: string, flag: string}> */
    public function languages(): array
    {
        $languages = [];
        foreach (config('app.supported_locales', []) as $code => $name) {
            $languages[$code] = ['name' => $name, 'flag' => 'images/flags/'.$code.'.svg'];
        }

        return $languages;
    }

    public function supports(string $locale): bool
    {
        return array_key_exists($locale, $this->languages());
    }

    public function remember(Request $request, string $locale): void
    {
        abort_unless($this->supports($locale), 404);
        $request->session()->put('locale', $locale);
    }

    /**
     * Use the visitor's chosen language, otherwise the default language from Admin › Platform settings.
     */
    public function apply(Request $request): void
    {
        $locale = $request->session()->get('locale');
        $platformDefault = (string) app(PlatformSettings::class)->get('localization.default_locale', '');
        $fallback = $this->supports($platformDefault) ? $platformDefault : config('app.fallback_locale', 'en');
        app()->setLocale(is_string($locale) && $this->supports($locale) ? $locale : $fallback);
    }
}
