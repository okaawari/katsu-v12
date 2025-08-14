<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScheduledPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'publishable_type',
        'publishable_id',
        'scheduled_by',
        'scheduled_for',
        'status',
        'published_at',
        'visibility',
        'notify_subscribers',
        'send_notifications',
        'failure_reason',
        'retry_count',
        'next_retry_at',
        'publication_settings',
        'notes',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'published_at' => 'datetime',
        'notify_subscribers' => 'boolean',
        'send_notifications' => 'boolean',
        'next_retry_at' => 'datetime',
        'publication_settings' => 'array',
    ];

    /**
     * Get the publishable model (episode, anime, etc.).
     */
    public function publishable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who scheduled the publication.
     */
    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }

    /**
     * Scope a query to only include pending publications.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include ready to publish.
     */
    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_for', '<=', now());
    }

    /**
     * Scope a query to only include failed publications.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query for retry ready publications.
     */
    public function scopeRetryReady($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_count', '<', 3)
                    ->where(function ($query) {
                        $query->whereNull('next_retry_at')
                              ->orWhere('next_retry_at', '<=', now());
                    });
    }

    /**
     * Check if the publication is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->scheduled_for->lt(now());
    }

    /**
     * Check if the publication can be retried.
     */
    public function getCanRetryAttribute(): bool
    {
        return $this->status === 'failed' && 
               $this->retry_count < 3 && 
               ($this->next_retry_at === null || $this->next_retry_at->lte(now()));
    }

    /**
     * Mark the publication as published.
     */
    public function markAsPublished(): bool
    {
        $this->status = 'published';
        $this->published_at = now();
        
        return $this->save();
    }

    /**
     * Mark the publication as failed.
     */
    public function markAsFailed(string $reason = null): bool
    {
        $this->status = 'failed';
        $this->failure_reason = $reason;
        $this->retry_count++;
        
        // Schedule next retry (exponential backoff)
        if ($this->retry_count < 3) {
            $this->next_retry_at = now()->addMinutes(pow(2, $this->retry_count) * 15);
        }
        
        return $this->save();
    }

    /**
     * Cancel the scheduled publication.
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        
        return $this->save();
    }

    /**
     * Execute the publication.
     */
    public function execute(): bool
    {
        try {
            $publishable = $this->publishable;
            
            if (!$publishable) {
                $this->markAsFailed('Publishable content not found');
                return false;
            }

            // Update the publishable content
            $publishable->update([
                'status' => 'published',
                'visibility' => $this->visibility,
                'published_at' => now(),
                'scheduled_at' => null,
            ]);

            // Mark this publication as completed
            $this->markAsPublished();

            // TODO: Send notifications if enabled
            if ($this->send_notifications) {
                // Dispatch notification jobs
            }

            return true;
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Reschedule the publication.
     */
    public function reschedule(\DateTime $newScheduledTime): bool
    {
        $this->scheduled_for = $newScheduledTime;
        $this->status = 'pending';
        $this->failure_reason = null;
        $this->retry_count = 0;
        $this->next_retry_at = null;
        
        return $this->save();
    }
}