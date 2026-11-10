<?php

namespace App\Http\Controllers\Admin\News;

use App\Enums\CommentStatus;
use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Comment moderation: approve, hide or delete reader comments, one at a time or in bulk.
 */
class CommentController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(CommentStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $status = CommentStatus::tryFrom($filters['status'] ?? '') ?? CommentStatus::Pending;

        return view('admin.news.comments.index', [
            'comments' => NewsComment::query()
                ->with(['article', 'user', 'moderator'])
                ->where('status', $status)
                ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('body', 'like', "%{$search}%"))
                ->orderBy('created_at', $status === CommentStatus::Pending ? 'asc' : 'desc')
                ->paginate(30)
                ->withQueryString(),
            'activeStatus' => $status,
            'statusCounts' => NewsComment::query()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'filters' => $filters,
        ]);
    }

    public function moderate(Request $request, NewsComment $comment): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Manage);
        $action = $request->validate(['action' => ['required', Rule::in(['approve', 'hide', 'delete'])]])['action'];

        $this->apply($request, collect([$comment]), $action);

        return back()->with('status', match ($action) {
            'approve' => 'Comment approved.',
            'hide' => 'Comment hidden.',
            default => 'Comment deleted.',
        });
    }

    public function bulk(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::News, PermissionAction::Manage);
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'hide', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
        ], ['ids.required' => 'Select at least one comment.']);

        $comments = NewsComment::whereKey($data['ids'])->get();
        $this->apply($request, $comments, $data['action']);

        return back()->with('status', "{$comments->count()} ".str($comments->count() === 1 ? 'comment' : 'comments').' updated.');
    }

    /**
     * @param  Collection<int, NewsComment>  $comments
     */
    private function apply(Request $request, Collection $comments, string $action): void
    {
        foreach ($comments as $comment) {
            if ($action === 'delete') {
                $this->auditLogger->log('news.comment.deleted', $comment, ['body' => $comment->body, 'status' => $comment->status->value]);
                $comment->delete();

                continue;
            }

            $oldStatus = $comment->status;
            $comment->update([
                'status' => $action === 'approve' ? CommentStatus::Approved : CommentStatus::Hidden,
                'moderated_by' => $request->user()->id,
                'moderated_at' => now(),
            ]);
            $this->auditLogger->log('news.comment.'.($action === 'approve' ? 'approved' : 'hidden'), $comment, ['status' => $oldStatus->value], ['status' => $comment->status->value]);
        }
    }
}
