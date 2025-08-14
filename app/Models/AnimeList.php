<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimeList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'anime_id',
        'status',
        'episodes_watched',
        'rewatches',
        'started_at',
        'completed_at',
        'last_watched_at',
        'user_rating',
        'review',
        'notes',
        'is_favorite',
        'is_private',
        'custom_tags',
        'priority',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime',
        'user_rating' => 'decimal:2',
        'is_favorite' => 'boolean',
        'is_private' => 'boolean',
        'custom_tags' => 'array',
    ];

    /**
     * Get the user that owns the list entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the anime in the list.
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Scope a query to only include public lists.
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope a query to only include favorites.
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->anime || $this->anime->total_episodes === 0) {
            return 0;
        }
        
        return round(($this->episodes_watched / $this->anime->total_episodes) * 100, 2);
    }

    /**
     * Check if the anime is completed by the user.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || 
               ($this->anime && $this->episodes_watched >= $this->anime->total_episodes);
    }

    /**
     * Get the user's rating as a formatted string.
     */
    public function getFormattedRatingAttribute(): ?string
    {
        if (!$this->user_rating) {
            return null;
        }
        
        return number_format($this->user_rating, 1) . '/10';
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'watching' => 'Currently Watching',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
            'dropped' => 'Dropped',
            'plan_to_watch' => 'Plan to Watch',
            default => 'Unknown'
        };
    }

    /**
     * Get the remaining episodes.
     */
    public function getRemainingEpisodesAttribute(): int
    {
        if (!$this->anime) {
            return 0;
        }
        
        return max(0, $this->anime->total_episodes - $this->episodes_watched);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        
        if ($this->anime) {
            $this->episodes_watched = $this->anime->total_episodes;
        }
        
        return $this->save();
    }

    /**
     * Update watching progress.
     */
    public function updateProgress(int $episodesWatched): bool
    {
        $this->episodes_watched = $episodesWatched;
        $this->last_watched_at = now();
        
        // Auto-update status based on progress
        if ($this->status === 'plan_to_watch' && $episodesWatched > 0) {
            $this->status = 'watching';
            $this->started_at = $this->started_at ?? now();
        }
        
        if ($this->anime && $episodesWatched >= $this->anime->total_episodes) {
            $this->status = 'completed';
            $this->completed_at = now();
        }
        
        return $this->save();
    }
}