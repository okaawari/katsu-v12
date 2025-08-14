<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'about',
        'avatar',
        'cover_image',
        'location',
        'website',
        'birth_date',
        'gender',
        'subscription_date',
        'subscription_expires_at',
        'subscription_type',
        'is_premium',
        'total_watch_time',
        'anime_watched',
        'episodes_watched',
        'average_rating_given',
        'reviews_count',
        'comments_count',
        'preferences',
        'timezone',
        'language',
        'last_active_at',
        'status',
        'status_reason',
        'profile_public',
        'show_watch_history',
        'show_favorites',
        'allow_friend_requests',
        'is_verified',
        'trust_score',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'subscription_date' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'is_premium' => 'boolean',
            'average_rating_given' => 'decimal:2',
            'preferences' => 'array',
            'last_active_at' => 'datetime',
            'profile_public' => 'boolean',
            'show_watch_history' => 'boolean',
            'show_favorites' => 'boolean',
            'allow_friend_requests' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the animes authored by the user.
     */
    public function authoredAnimes(): HasMany
    {
        return $this->hasMany(Anime::class, 'author_id');
    }

    /**
     * Get the episodes uploaded by the user.
     */
    public function uploadedEpisodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'uploaded_by');
    }

    /**
     * Get the user's anime lists.
     */
    public function animeLists(): HasMany
    {
        return $this->hasMany(AnimeList::class);
    }

    /**
     * Get the user's badges.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
                    ->withPivot(['awarded_at', 'reason', 'context', 'is_visible', 'is_featured'])
                    ->withTimestamps();
    }

    /**
     * Get the user's badge assignments.
     */
    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    /**
     * Get the user's comments.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the user's video watch progress.
     */
    public function watchProgress(): HasMany
    {
        return $this->hasMany(VideoWatchProgress::class);
    }

    /**
     * Get the user's ratings.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the user's views.
     */
    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Check if the user has an active premium subscription.
     */
    public function getHasActivePremiumAttribute(): bool
    {
        return $this->is_premium && 
               $this->subscription_expires_at && 
               $this->subscription_expires_at->gt(now());
    }

    /**
     * Get the user's favorite animes.
     */
    public function favoriteAnimes()
    {
        return $this->animeLists()->where('is_favorite', true)->with('anime');
    }

    /**
     * Get the user's currently watching animes.
     */
    public function watchingAnimes()
    {
        return $this->animeLists()->where('status', 'watching')->with('anime');
    }

    /**
     * Get the user's completed animes.
     */
    public function completedAnimes()
    {
        return $this->animeLists()->where('status', 'completed')->with('anime');
    }

    /**
     * Get visible badges for the user.
     */
    public function visibleBadges()
    {
        return $this->userBadges()
                    ->where('is_visible', true)
                    ->whereNull('revoked_at')
                    ->with('badge')
                    ->orderBy('display_order');
    }

    /**
     * Get featured badges for the user.
     */
    public function featuredBadges()
    {
        return $this->userBadges()
                    ->where('is_featured', true)
                    ->where('is_visible', true)
                    ->whereNull('revoked_at')
                    ->with('badge')
                    ->orderBy('display_order');
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for premium users.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true)
                    ->where('subscription_expires_at', '>', now());
    }

    /**
     * Update the user's last active timestamp.
     */
    public function updateLastActive(): bool
    {
        $this->last_active_at = now();
        return $this->save(['timestamps' => false]);
    }
}
