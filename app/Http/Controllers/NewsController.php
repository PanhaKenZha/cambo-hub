<?php

namespace App\Http\Controllers;

use App\Enums\CommentStatus;
use App\Models\NewsArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public news article page and reader comments. Developer 2 may restyle the views.
 */
class NewsController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $article = NewsArticle::live()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
        $article->increment('views_count');

        return view('news.show', [
            'article' => $article,
            'locale' => app()->getLocale(),
            'comments' => $article->comments()->with('user')->where('status', CommentStatus::Approved)->latest()->limit(100)->get(),
            'isPreview' => false,
        ]);
    }

    /**
     * Comments wait for a moderator before they appear.
     */
    public function comment(Request $request, string $slug): RedirectResponse
    {
        $article = NewsArticle::live()->where('slug', $slug)->firstOrFail();
        abort_unless($article->allow_comments, 403);

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:2000']]);

        $article->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'status' => CommentStatus::Pending,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', __('Thanks! Your comment will appear after a moderator reviews it.'));
    }
}
