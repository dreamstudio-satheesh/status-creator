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
        Schema::table('ai_generation_logs', function (Blueprint $table) {
            $table->integer('response_time_ms')->nullable()->after('cost');
            $table->string('provider', 50)->nullable()->after('model_used');
            $table->text('metadata')->nullable()->after('response_time_ms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generation_logs', function (Blueprint $table) {
            $table->dropColumn(['response_time_ms', 'provider', 'metadata']);
        });
    }
};
