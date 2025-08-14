<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'title_english',
        'title_japanese',
        'slug',
        'description',
        'content_rating',
        'status',
        'total_episodes',
        'current_episode',
        'cover_image',
        'banner_image',
        'average_rating',
        'rating_count',
        'view_count',
        'favorite_count',
        'visibility',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'meta_keywords' => 'array',
    ];

    /**
     * Get the category that owns the anime.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the author that owns the anime.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the episodes for the anime.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Get the tags for the anime.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'anime_tag')
                    ->withTimestamps();
    }

    /**
     * Get the anime lists for the anime.
     */
    public function animeLists(): HasMany
    {
        return $this->hasMany(AnimeList::class);
    }

    /**
     * Get the comments for the anime.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the views for the anime.
     */
    public function views(): MorphMany
    {
        return $this->morphMany(View::class, 'viewable');
    }

    /**
     * Get the ratings for the anime.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    /**
     * Scope a query to only include published animes.
     */
    public function scopePublished($query)
    {
        return $query->where('visibility', 'public')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured animes.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the series completion percentage.
     */
    public function getCompletionPercentageAttribute(): float
    {
        if ($this->total_episodes === 0) {
            return 0;
        }
        
        return round(($this->current_episode / $this->total_episodes) * 100, 2);
    }

    /**
     * Check if the series is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || $this->current_episode >= $this->total_episodes;
    }

    /**
     * Get published episodes for the series.
     */
    public function publishedEpisodes()
    {
        return $this->episodes()
                    ->where('status', 'published')
                    ->where('visibility', '!=', 'private')
                    ->where(function ($query) {
                        $query->whereNull('scheduled_at')
                              ->orWhere('scheduled_at', '<=', now());
                    })
                    ->orderBy('episode_number');
    }

    /**
     * Get the latest published episode.
     */
    public function latestEpisode()
    {
        return $this->publishedEpisodes()->latest('published_at')->first();
    }

    /**
     * Get episodes count by status.
     */
    public function getEpisodesCountByStatus(string $status): int
    {
        return $this->episodes()->where('status', $status)->count();
    }
}