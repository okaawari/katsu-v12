<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Episode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'anime_id',
        'uploaded_by',
        'episode_number',
        'title',
        'title_english',
        'title_japanese',
        'synopsis',
        'slug',
        'poster_image',
        'thumbnail_image',
        'preview_images',
        'video_480p',
        'video_720p',
        'video_1080p',
        'video_4k',
        'subtitle_english',
        'subtitle_mongolian',
        'subtitle_tracks',
        'duration',
        'duration_seconds',
        'sprite_vtt',
        'sprite_image',
        'sprite_columns',
        'sprite_rows',
        'sprite_interval',
        'video_codec',
        'audio_codec',
        'file_size',
        'bitrate',
        'resolution',
        'fps',
        'status',
        'visibility',
        'scheduled_at',
        'published_at',
        'is_featured',
        'is_premium',
        'content_tags',
        'content_warnings',
        'view_count',
        'average_rating',
        'rating_count',
        'favorite_count',
        'server_location',
        'cdn_urls',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'preview_images' => 'array',
        'subtitle_tracks' => 'array',
        'cdn_urls' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_premium' => 'boolean',
        'content_tags' => 'array',
        'average_rating' => 'decimal:2',
        'fps' => 'decimal:2',
        'sprite_interval' => 'decimal:2',
        'meta_keywords' => 'array',
    ];

    /**
     * Get the anime that owns the episode.
     */
    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    /**
     * Get the user who uploaded the episode.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the watch progress records for the episode.
     */
    public function watchProgress(): HasMany
    {
        return $this->hasMany(VideoWatchProgress::class);
    }

    /**
     * Get the comments for the episode.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the views for the episode.
     */
    public function views(): MorphMany
    {
        return $this->morphMany(View::class, 'viewable');
    }

    /**
     * Get the ratings for the episode.
     */
    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'ratable');
    }

    /**
     * Scope a query to only include published episodes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('visibility', '!=', 'private')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include scheduled episodes.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->whereNotNull('scheduled_at');
    }

    /**
     * Scope a query to only include episodes ready to be published.
     */
    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope a query to only include public episodes.
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope a query to only include featured episodes.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include premium episodes.
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope a query to only include free episodes.
     */
    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the episode's formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return $this->duration ?? 'Unknown';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get the best available video quality.
     */
    public function getBestVideoQualityAttribute(): ?string
    {
        if ($this->video_4k) return $this->video_4k;
        if ($this->video_1080p) return $this->video_1080p;
        if ($this->video_720p) return $this->video_720p;
        if ($this->video_480p) return $this->video_480p;
        
        return null;
    }

    /**
     * Get available video qualities.
     */
    public function getAvailableQualitiesAttribute(): array
    {
        $qualities = [];
        
        if ($this->video_480p) $qualities['480p'] = $this->video_480p;
        if ($this->video_720p) $qualities['720p'] = $this->video_720p;
        if ($this->video_1080p) $qualities['1080p'] = $this->video_1080p;
        if ($this->video_4k) $qualities['4k'] = $this->video_4k;
        
        return $qualities;
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if episode has sprite thumbnails.
     */
    public function getHasSpritesAttribute(): bool
    {
        return !empty($this->sprite_vtt) && !empty($this->sprite_image);
    }

    /**
     * Check if the episode is scheduled for future publication.
     */
    public function getIsScheduledAttribute(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at && 
               $this->scheduled_at->gt(now());
    }

    /**
     * Check if the episode is ready to be published.
     */
    public function getIsReadyToPublishAttribute(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at && 
               $this->scheduled_at->lte(now());
    }

    /**
     * Check if the episode is publicly viewable.
     */
    public function getIsPubliclyViewableAttribute(): bool
    {
        return $this->status === 'published' && 
               $this->visibility === 'public' &&
               ($this->published_at === null || $this->published_at->lte(now()));
    }

    /**
     * Check if the episode is hidden.
     */
    public function getIsHiddenAttribute(): bool
    {
        return $this->status === 'hidden' || $this->visibility === 'private';
    }

    /**
     * Get the episode's main display image (poster or thumbnail).
     */
    public function getDisplayImageAttribute(): string
    {
        return $this->poster_image ?: $this->thumbnail_image;
    }

    /**
     * Schedule the episode for publication.
     */
    public function scheduleForPublication(\DateTime $scheduledAt): bool
    {
        $this->status = 'scheduled';
        $this->scheduled_at = $scheduledAt;
        
        return $this->save();
    }

    /**
     * Publish the episode immediately.
     */
    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = now();
        $this->scheduled_at = null;
        
        return $this->save();
    }

    /**
     * Hide the episode from public view.
     */
    public function hide(): bool
    {
        $this->status = 'hidden';
        
        return $this->save();
    }

    /**
     * Set the episode as premium content.
     */
    public function makePremium(): bool
    {
        $this->is_premium = true;
        
        return $this->save();
    }

    /**
     * Set the episode as free content.
     */
    public function makeFree(): bool
    {
        $this->is_premium = false;
        
        return $this->save();
    }
}