<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'background_color',
        'border_color',
        'image',
        'tier',
        'points',
        'order',
        'requirements',
        'metadata',
        'is_active',
        'is_visible',
        'is_revokable',
        'is_stackable',
        'is_automatic',
        'rarity',
        'available_from',
        'available_until',
        'max_recipients',
    ];

    protected $casts = [
        'requirements' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_revokable' => 'boolean',
        'is_stackable' => 'boolean',
        'is_automatic' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    /**
     * Get the user badges for the badge.
     */
    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get the users that have this badge.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
                    ->withPivot(['awarded_at', 'reason', 'context', 'is_visible', 'is_featured'])
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include active badges.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include visible badges.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include automatic badges.
     */
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    /**
     * Scope a query to filter by tier.
     */
    public function scopeTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }

    /**
     * Scope a query to filter by rarity.
     */
    public function scopeRarity($query, $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if the badge is currently available.
     */
    public function getIsAvailableAttribute(): bool
    {
        $now = now();
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }
        
        return $this->is_active;
    }

    /**
     * Check if the badge has reached its maximum recipients.
     */
    public function getIsAtMaxRecipientsAttribute(): bool
    {
        if (!$this->max_recipients) {
            return false;
        }
        
        return $this->userBadges()->count() >= $this->max_recipients;
    }

    /**
     * Get the badge's color scheme as an array.
     */
    public function getColorSchemeAttribute(): array
    {
        return [
            'primary' => $this->color,
            'background' => $this->background_color,
            'border' => $this->border_color,
        ];
    }
}