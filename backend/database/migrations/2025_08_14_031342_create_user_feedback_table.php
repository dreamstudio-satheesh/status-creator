<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('general'); // general, bug, feature_request, support
            $table->string('subject', 255);
            $table->text('message');
            $table->tinyInteger('rating')->unsigned()->nullable(); // 1-5 stars for app rating
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->json('metadata')->nullable(); // device info, app version, etc.
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};