<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'parent_id',
        'commentable_type',
        'commentable_id',
        'content',
        'content_html',
        'status',
        'moderation_reason',
        'moderated_by',
        'moderated_at',
        'likes_count',
        'dislikes_count',
        'replies_count',
        'ip_address',
        'user_agent',
        'is_edited',
        'edited_at',
        'published_at',
    ];

    protected $casts = [
        'moderated_at' => 'datetime',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the comment replies.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get the commentable model (anime or episode).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the moderator who moderated the comment.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope a query to only include published comments.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include root comments (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to include replies to a specific comment.
     */
    public function scopeRepliesTo($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Check if the comment is a reply.
     */
    public function getIsReplyAttribute(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if the comment has replies.
     */
    public function getHasRepliesAttribute(): bool
    {
        return $this->replies_count > 0;
    }

    /**
     * Get the comment depth level.
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $comment = $this;
        
        while ($comment->parent) {
            $depth++;
            $comment = $comment->parent;
        }
        
        return $depth;
    }

    /**
     * Check if the comment is moderated.
     */
    public function getIsModeratedAttribute(): bool
    {
        return !is_null($this->moderated_at);
    }

    /**
     * Mark the comment as edited.
     */
    public function markAsEdited(): bool
    {
        $this->is_edited = true;
        $this->edited_at = now();
        
        return $this->save();
    }

    /**
     * Moderate the comment.
     */
    public function moderate(string $status, User $moderator, string $reason = null): bool
    {
        $this->status = $status;
        $this->moderated_by = $moderator->id;
        $this->moderated_at = now();
        $this->moderation_reason = $reason;
        
        return $this->save();
    }

    /**
     * Increment the replies count.
     */
    public function incrementRepliesCount(): bool
    {
        return $this->increment('replies_count');
    }

    /**
     * Decrement the replies count.
     */
    public function decrementRepliesCount(): bool
    {
        return $this->decrement('replies_count');
    }
}