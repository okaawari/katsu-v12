<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class View extends Model
{
    use HasFactory;

    protected $fillable = [
        'viewable_type',
        'viewable_id',
        'user_id',
        'visitor_id',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'region',
        'city',
        'device_type',
        'browser',
        'platform',
        'collection',
        'metadata',
        'duration_seconds',
        'viewed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the user that made the view.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the viewable model (anime, episode, etc.).
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query for a specific viewable type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('viewable_type', $type);
    }

    /**
     * Scope a query for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query for a specific visitor.
     */
    public function scopeForVisitor($query, $visitorId)
    {
        return $query->where('visitor_id', $visitorId);
    }

    /**
     * Scope a query for a specific collection.
     */
    public function scopeForCollection($query, $collection)
    {
        return $query->where('collection', $collection);
    }

    /**
     * Scope a query for a specific country.
     */
    public function scopeFromCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query for a specific device type.
     */
    public function scopeFromDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope a query for a date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('viewed_at', [$from, $to]);
    }

    /**
     * Scope a query for today's views.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', today());
    }

    /**
     * Scope a query for this week's views.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope a query for this month's views.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('viewed_at', now()->month)
                    ->whereYear('viewed_at', now()->year);
    }

    /**
     * Check if the view is from an authenticated user.
     */
    public function getIsAuthenticatedAttribute(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Check if the view is from a mobile device.
     */
    public function getIsMobileAttribute(): bool
    {
        return $this->device_type === 'mobile';
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_seconds) {
            return null;
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return sprintf('%d:%02d', $minutes, $seconds);
        }
        
        return sprintf('0:%02d', $seconds);
    }

    /**
     * Get the geographic location as a string.
     */
    public function getLocationAttribute(): ?string
    {
        $location = array_filter([$this->city, $this->region, $this->country]);
        
        return !empty($location) ? implode(', ', $location) : null;
    }

    /**
     * Create a new view record.
     */
    public static function record($viewable, $data = []): self
    {
        return self::create(array_merge([
            'viewable_type' => get_class($viewable),
            'viewable_id' => $viewable->getKey(),
            'viewed_at' => now(),
        ], $data));
    }
}