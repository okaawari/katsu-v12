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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('ratable'); // ratable_type, ratable_id (anime or episode)
            
            // Rating Information
            $table->decimal('rating', 3, 2); // 1.00 to 10.00
            $table->text('review')->nullable();
            $table->json('criteria_ratings')->nullable(); // Story, animation, sound, etc.
            
            // Interaction Stats
            $table->integer('helpful_count')->default(0);
            $table->integer('unhelpful_count')->default(0);
            
            // Moderation
            $table->enum('status', ['published', 'pending', 'hidden', 'flagged'])->default('published');
            $table->text('moderation_reason')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Metadata
            $table->boolean('is_spoiler')->default(false);
            $table->boolean('is_recommended')->nullable(); // True = recommend, False = don't recommend
            $table->json('tags')->nullable(); // User-defined tags for the rating
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'ratable_type', 'ratable_id']);
            $table->index(['ratable_type', 'ratable_id', 'status']);
            $table->index(['user_id', 'rating']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};