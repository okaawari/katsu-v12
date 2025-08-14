<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_id',
        'awarded_by',
        'reason',
        'context',
        'awarded_at',
        'revoked_at',
        'revoke_reason',
        'is_visible',
        'is_featured',
        'display_order',
        'progress_current',
        'progress_target',
        'progress_percentage',
    ];

    protected $casts = [
        'context' => 'array',
        'awarded_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'progress_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the badge.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge that is assigned.
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the user who awarded the badge.
     */
    public function awardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /**
     * Scope a query to only include visible badges.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include featured badges.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include active (not revoked) badges.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Check if the badge is revoked.
     */
    public function getIsRevokedAttribute(): bool
    {
        return !is_null($this->revoked_at);
    }

    /**
     * Check if the badge is completed (for progressive badges).
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->progress_current >= $this->progress_target;
    }

    /**
     * Revoke the badge.
     */
    public function revoke(string $reason = null, User $revokedBy = null): bool
    {
        $this->revoked_at = now();
        $this->revoke_reason = $reason;
        
        if ($revokedBy) {
            $this->revoked_by = $revokedBy->id;
        }
        
        return $this->save();
    }

    /**
     * Update the progress for progressive badges.
     */
    public function updateProgress(int $current): bool
    {
        $this->progress_current = $current;
        $this->progress_percentage = $this->progress_target > 0 
            ? min(100, ($current / $this->progress_target) * 100) 
            : 0;
        
        return $this->save();
    }
}