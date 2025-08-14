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
        Schema::create('animes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            
            // Basic Information
            $table->string('title');
            $table->string('title_english')->nullable();
            $table->string('title_japanese')->nullable();
            $table->string('title_romanji')->nullable();
            $table->string('slug')->unique();
            
            // Content Information
            $table->longText('synopsis')->nullable();
            $table->text('description')->nullable();
            $table->string('studio');
            $table->string('source')->nullable(); // manga, light novel, original, etc.
            $table->string('rating')->default('PG-13'); // G, PG, PG-13, R, R+
            
            // Release Information
            $table->string('status')->default('ongoing'); // completed, ongoing, upcoming, cancelled
            $table->date('aired_from')->nullable();
            $table->date('aired_to')->nullable();
            $table->string('season')->nullable(); // spring, summer, fall, winter
            $table->integer('year')->nullable();
            
            // Episode Information
            $table->integer('total_episodes')->default(1);
            $table->integer('current_episode')->default(0);
            $table->string('episode_duration')->nullable(); // average episode duration
            
            // Media
            $table->string('poster_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->json('gallery')->nullable(); // additional images
            
            // Statistics
            $table->decimal('average_rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('favorite_count')->default(0);
            
            // Publishing
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            
            // SEO & Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'visibility']);
            $table->index(['year', 'season']);
            $table->index(['is_featured', 'published_at']);
            $table->fullText(['title', 'title_english', 'synopsis']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animes');
    }
};