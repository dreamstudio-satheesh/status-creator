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
            $table->string('service_type', 50)->nullable()->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_generation_logs', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
