<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            
            // Episode Information
            $table->integer('episode_number');
            $table->string('title')->nullable();
            $table->string('title_english')->nullable();
            $table->string('title_japanese')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('slug');
            
            // Video Files
            $table->string('video_480p')->nullable();
            $table->string('video_720p')->nullable();
            $table->string('video_1080p')->nullable();
            $table->string('video_4k')->nullable();
            
            // Subtitles
            $table->string('subtitle_english')->nullable();
            $table->string('subtitle_mongolian')->nullable();
            $table->json('subtitle_tracks')->nullable(); // Additional subtitle languages
            
            // Video Metadata
            $table->string('duration')->nullable(); // HH:MM:SS format
            $table->integer('duration_seconds')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('sprite_vtt')->nullable(); // VTT file for video thumbnails
            $table->string('sprite_image')->nullable(); // Sprite image file
            $table->integer('sprite_columns')->nullable(); // Number of columns in sprite
            $table->integer('sprite_rows')->nullable(); // Number of rows in sprite
            $table->decimal('sprite_interval', 8, 2)->nullable(); // Seconds between thumbnails
            
            // Technical Information
            $table->string('video_codec')->nullable();
            $table->string('audio_codec')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->integer('bitrate')->nullable();
            $table->string('resolution')->nullable(); // 1920x1080
            $table->decimal('fps', 5, 2)->nullable();
            
            // Publishing
            $table->enum('status', ['processing', 'ready', 'failed', 'private'])->default('processing');
            $table->timestamp('aired_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Statistics
            $table->integer('view_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            
            // Server Information
            $table->string('server_location')->nullable();
            $table->json('cdn_urls')->nullable(); // Multiple CDN endpoints
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['anime_id', 'episode_number']);
            $table->index(['anime_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};