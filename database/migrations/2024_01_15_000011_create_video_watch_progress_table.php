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
        Schema::create('video_watch_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->constrained()->onDelete('cascade');
            
            // Progress Information
            $table->decimal('current_time', 10, 2)->default(0.00); // Current position in seconds
            $table->decimal('duration', 10, 2)->nullable(); // Total video duration in seconds
            $table->decimal('progress_percentage', 5, 2)->default(0.00); // 0.00 to 100.00
            
            // Watch Status
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_skipped')->default(false);
            $table->integer('watch_count')->default(1); // How many times watched
            
            // Quality & Playback Info
            $table->string('quality_watched')->nullable(); // 480p, 720p, 1080p, 4k
            $table->string('subtitle_language')->nullable(); // Which subtitle was used
            $table->decimal('playback_speed', 3, 2)->default(1.00); // Playback speed
            
            // Device & Session Info
            $table->string('device_type')->nullable(); // mobile, desktop, tablet, tv
            $table->string('platform')->nullable(); // web, android, ios
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_position_update')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'episode_id']);
            $table->index(['user_id', 'is_completed']);
            $table->index(['episode_id', 'progress_percentage']);
            $table->index('last_position_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_watch_progress');
    }
};