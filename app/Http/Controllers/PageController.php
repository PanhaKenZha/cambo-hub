<?php

namespace App\Http\Controllers;

use App\Services\SiteContent;
use Illuminate\View\View;

/**
 * Public CMS page, e.g. /pages/about-us. Developer 2 may restyle the view.
 */
class PageController extends Controller
{
    public function __invoke(string $slug, SiteContent $siteContent): View
    {
        $page = $siteContent->page($slug);

        abort_if($page === null, 404);

        return view('pages.show', [
            'title' => $page['title'],
            'html' => $page['html'],
            'metaDescription' => $page['meta_description'],
            'isPreview' => false,
        ]);
    }
}
