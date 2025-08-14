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
        'video_480p',
        'video_720p',
        'video_1080p',
        'video_4k',
        'subtitle_english',
        'subtitle_mongolian',
        'subtitle_tracks',
        'duration',
        'duration_seconds',
        'thumbnail',
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
        'aired_at',
        'published_at',
        'view_count',
        'average_rating',
        'rating_count',
        'server_location',
        'cdn_urls',
    ];

    protected $casts = [
        'subtitle_tracks' => 'array',
        'cdn_urls' => 'array',
        'aired_at' => 'datetime',
        'published_at' => 'datetime',
        'average_rating' => 'decimal:2',
        'fps' => 'decimal:2',
        'sprite_interval' => 'decimal:2',
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
     * Scope a query to only include ready episodes.
     */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /**
     * Scope a query to only include published episodes.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'ready')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
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
}