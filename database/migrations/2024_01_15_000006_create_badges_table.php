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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Visual Properties
            $table->string('icon')->nullable(); // Icon class or file path
            $table->string('color', 7)->default('#3B82F6'); // Primary color
            $table->string('background_color', 7)->default('#EFF6FF'); // Background color
            $table->string('border_color', 7)->default('#3B82F6'); // Border color
            $table->string('image')->nullable(); // Badge image/logo
            
            // Badge Properties
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'diamond', 'special'])->default('bronze');
            $table->integer('points')->default(0); // Points value of the badge
            $table->integer('order')->default(0); // Display order
            
            // Requirements & Rules
            $table->json('requirements')->nullable(); // JSON requirements for earning
            $table->json('metadata')->nullable(); // Additional badge data
            
            // Behavior
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_revokable')->default(false);
            $table->boolean('is_stackable')->default(false); // Can be earned multiple times
            $table->boolean('is_automatic')->default(false); // Automatically awarded
            
            // Rarity & Availability
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic', 'legendary'])->default('common');
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->integer('max_recipients')->nullable(); // Limited edition badges
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tier', 'is_active']);
            $table->index(['rarity', 'is_visible']);
            $table->index(['is_automatic', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};