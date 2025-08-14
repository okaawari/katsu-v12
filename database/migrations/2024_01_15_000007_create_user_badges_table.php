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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Award Information
            $table->text('reason')->nullable(); // Why the badge was awarded
            $table->json('context')->nullable(); // Additional context (anime_id, episode_id, etc.)
            $table->timestamp('awarded_at');
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();
            
            // Display Properties
            $table->boolean('is_visible')->default(true); // User can hide badges
            $table->boolean('is_featured')->default(false); // Show prominently on profile
            $table->integer('display_order')->default(0);
            
            // Progress Tracking (for progressive badges)
            $table->integer('progress_current')->default(0);
            $table->integer('progress_target')->default(1);
            $table->decimal('progress_percentage', 5, 2)->default(0.00);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_visible']);
            $table->index(['badge_id', 'awarded_at']);
            $table->index(['user_id', 'is_featured']);
            $table->unique(['user_id', 'badge_id']); // Assuming non-stackable by default
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};