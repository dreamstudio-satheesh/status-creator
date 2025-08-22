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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('background_image', 500)->nullable();
            $table->text('quote_text'); // English quote
            $table->text('quote_text_ta')->nullable(); // Tamil quote
            $table->string('font_family', 100)->default('Tamil');
            $table->integer('font_size')->default(24);
            $table->string('text_color', 7)->default('#FFFFFF');
            $table->enum('text_alignment', ['left', 'center', 'right'])->default('center');
            $table->integer('padding')->default(20);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->boolean('ai_generated')->default(false);
            $table->text('image_caption')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('theme_id');
            $table->index(['is_premium', 'is_featured']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
