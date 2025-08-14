<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ratable_type',
        'ratable_id',
        'rating',
        'review',
        'criteria_ratings',
        'helpful_count',
        'unhelpful_count',
        'status',
        'moderation_reason',
        'moderated_by',
        'is_spoiler',
        'is_recommended',
        'tags',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'criteria_ratings' => 'array',
        'is_spoiler' => 'boolean',
        'is_recommended' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the user that owns the rating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ratable model (anime or episode).
     */
    public function ratable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the moderator who moderated the rating.
     */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /**
     * Scope a query to only include published ratings.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include pending ratings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to filter by rating range.
     */
    public function scopeRatingRange($query, $min, $max)
    {
        return $query->whereBetween('rating', [$min, $max]);
    }

    /**
     * Scope a query to only include reviews (ratings with text).
     */
    public function scopeWithReview($query)
    {
        return $query->whereNotNull('review');
    }

    /**
     * Scope a query to exclude spoilers.
     */
    public function scopeNoSpoilers($query)
    {
        return $query->where('is_spoiler', false);
    }

    /**
     * Scope a query to only include recommended ratings.
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommended', true);
    }

    /**
     * Scope a query for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the rating as a star representation.
     */
    public function getStarsAttribute(): int
    {
        return (int) round($this->rating / 2); // Convert 10-point scale to 5-star
    }

    /**
     * Get the rating formatted as a string.
     */
    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->rating, 1) . '/10';
    }

    /**
     * Check if the rating has a review.
     */
    public function getHasReviewAttribute(): bool
    {
        return !empty($this->review);
    }

    /**
     * Get the helpfulness ratio.
     */
    public function getHelpfulnessRatioAttribute(): float
    {
        $total = $this->helpful_count + $this->unhelpful_count;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($this->helpful_count / $total) * 100, 2);
    }

    /**
     * Check if the rating is positive (7+ out of 10).
     */
    public function getIsPositiveAttribute(): bool
    {
        return $this->rating >= 7;
    }

    /**
     * Check if the rating is negative (4 or less out of 10).
     */
    public function getIsNegativeAttribute(): bool
    {
        return $this->rating <= 4;
    }

    /**
     * Get the rating color based on score.
     */
    public function getRatingColorAttribute(): string
    {
        return match(true) {
            $this->rating >= 8 => 'green',
            $this->rating >= 6 => 'yellow',
            $this->rating >= 4 => 'orange',
            default => 'red'
        };
    }

    /**
     * Get individual criteria rating.
     */
    public function getCriteriaRating(string $criteria): ?float
    {
        if (!$this->criteria_ratings || !isset($this->criteria_ratings[$criteria])) {
            return null;
        }
        
        return (float) $this->criteria_ratings[$criteria];
    }

    /**
     * Set individual criteria rating.
     */
    public function setCriteriaRating(string $criteria, float $rating): void
    {
        $criteriaRatings = $this->criteria_ratings ?? [];
        $criteriaRatings[$criteria] = $rating;
        $this->criteria_ratings = $criteriaRatings;
    }

    /**
     * Mark the rating as helpful.
     */
    public function markHelpful(): bool
    {
        return $this->increment('helpful_count');
    }

    /**
     * Mark the rating as unhelpful.
     */
    public function markUnhelpful(): bool
    {
        return $this->increment('unhelpful_count');
    }

    /**
     * Moderate the rating.
     */
    public function moderate(string $status, User $moderator, string $reason = null): bool
    {
        $this->status = $status;
        $this->moderated_by = $moderator->id;
        $this->moderation_reason = $reason;
        
        return $this->save();
    }
}