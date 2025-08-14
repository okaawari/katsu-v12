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
        Schema::create('anime_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('anime_id')->constrained()->onDelete('cascade');
            
            // List Status
            $table->enum('status', [
                'watching', 
                'completed', 
                'on_hold', 
                'dropped', 
                'plan_to_watch'
            ])->default('plan_to_watch');
            
            // Progress Tracking
            $table->integer('episodes_watched')->default(0);
            $table->integer('rewatches')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_watched_at')->nullable();
            
            // User Rating & Review
            $table->decimal('user_rating', 3, 2)->nullable(); // 1.00 to 10.00
            $table->text('review')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_favorite')->default(false);
            
            // Privacy
            $table->boolean('is_private')->default(false);
            
            // Custom fields
            $table->json('custom_tags')->nullable(); // User's custom tags
            $table->integer('priority')->default(0); // Watch priority
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'anime_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_favorite']);
            $table->index(['anime_id', 'user_rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_lists');
    }
};