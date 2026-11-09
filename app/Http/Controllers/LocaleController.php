<?php

namespace App\Http\Controllers;

use App\Services\LocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Remember the visitor's chosen language and return them to the page they were on.
     */
    public function __invoke(Request $request, string $locale, LocaleService $languages): RedirectResponse
    {
        $languages->remember($request, $locale);
        $previous = url()->previous();
        $origin = $request->getSchemeAndHttpHost();

        return redirect()->to(str_starts_with($previous, $origin.'/') ? $previous : route('home'));
    }
}
