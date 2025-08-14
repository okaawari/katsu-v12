<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoWatchProgress extends Model
{
    use HasFactory;

    protected $table = 'video_watch_progress';

    protected $fillable = [
        'user_id',
        'episode_id',
        'current_time',
        'duration',
        'progress_percentage',
        'is_completed',
        'is_skipped',
        'watch_count',
        'quality_watched',
        'subtitle_language',
        'playback_speed',
        'device_type',
        'platform',
        'ip_address',
        'user_agent',
        'started_at',
        'completed_at',
        'last_position_update',
    ];

    protected $casts = [
        'current_time' => 'decimal:2',
        'duration' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'is_completed' => 'boolean',
        'is_skipped' => 'boolean',
        'playback_speed' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_position_update' => 'datetime',
    ];

    /**
     * Get the user that owns the watch progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the episode being watched.
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * Scope a query to only include completed watches.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope a query to only include in-progress watches.
     */
    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false)
                    ->where('current_time', '>', 0);
    }

    /**
     * Scope a query for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query for a specific episode.
     */
    public function scopeForEpisode($query, $episodeId)
    {
        return $query->where('episode_id', $episodeId);
    }

    /**
     * Get the formatted current time.
     */
    public function getFormattedCurrentTimeAttribute(): string
    {
        return $this->formatTime($this->current_time);
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        return $this->formatTime($this->duration);
    }

    /**
     * Get the remaining time.
     */
    public function getRemainingTimeAttribute(): float
    {
        if (!$this->duration) {
            return 0;
        }
        
        return max(0, $this->duration - $this->current_time);
    }

    /**
     * Get the formatted remaining time.
     */
    public function getFormattedRemainingTimeAttribute(): string
    {
        return $this->formatTime($this->remaining_time);
    }

    /**
     * Check if the episode is almost completed (90% or more).
     */
    public function getIsAlmostCompletedAttribute(): bool
    {
        return $this->progress_percentage >= 90;
    }

    /**
     * Update the watch progress.
     */
    public function updateProgress(float $currentTime, float $duration = null): bool
    {
        $this->current_time = $currentTime;
        $this->last_position_update = now();
        
        if ($duration) {
            $this->duration = $duration;
        }
        
        // Calculate progress percentage
        if ($this->duration > 0) {
            $this->progress_percentage = min(100, ($currentTime / $this->duration) * 100);
        }
        
        // Mark as completed if progress is 95% or more
        if ($this->progress_percentage >= 95 && !$this->is_completed) {
            $this->markCompleted();
        }
        
        return $this->save();
    }

    /**
     * Mark the episode as completed.
     */
    public function markCompleted(): bool
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->progress_percentage = 100;
        
        return $this->save();
    }

    /**
     * Mark the episode as skipped.
     */
    public function markSkipped(): bool
    {
        $this->is_skipped = true;
        $this->is_completed = true;
        $this->completed_at = now();
        
        return $this->save();
    }

    /**
     * Increment the watch count.
     */
    public function incrementWatchCount(): bool
    {
        $this->watch_count++;
        $this->started_at = now();
        
        return $this->save();
    }

    /**
     * Format time in seconds to HH:MM:SS or MM:SS format.
     */
    private function formatTime(float $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }
        
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}