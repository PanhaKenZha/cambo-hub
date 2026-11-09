<?php

namespace App\Models;

use Database\Factories\ApprovalEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One step in an approval request's review history.
 */
#[Fillable(['approval_request_id', 'actor_id', 'action', 'note'])]
class ApprovalEvent extends Model
{
    /** @use HasFactory<ApprovalEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<ApprovalRequest, $this>
     */
    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function label(): string
    {
        return match ($this->action) {
            'submitted' => 'Submitted for review',
            'resubmitted' => 'Resubmitted for review',
            'assigned' => 'Assigned',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'appeal_overturned' => 'Approved on appeal',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }
}
