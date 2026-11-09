<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Render the homepage from CMS settings and section content.
     */
    public function __invoke(CmsService $cms): View
    {
        return view('home', [
            'hero' => $cms->setting('hero', []),
            'search' => $cms->setting('search', []),
            'membership' => $cms->setting('membership', []),
            'blocks' => $cms->homepageSections(),
        ]);
    }
}
