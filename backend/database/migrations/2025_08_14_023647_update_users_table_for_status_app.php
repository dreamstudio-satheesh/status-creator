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
        Schema::table('users', function (Blueprint $table) {
            // Add mobile field
            $table->string('mobile', 20)->unique()->nullable()->after('email');
            
            // Add Google OAuth
            $table->string('google_id')->nullable()->after('password');
            $table->string('avatar')->nullable()->after('google_id');
            
            // Add subscription fields
            $table->enum('subscription_type', ['free', 'premium'])->default('free')->after('avatar');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_type');
            
            // Add daily quota tracking
            $table->integer('daily_ai_quota')->default(10)->after('subscription_expires_at');
            $table->integer('daily_ai_used')->default(0)->after('daily_ai_quota');
            $table->date('last_quota_reset')->nullable()->after('daily_ai_used');
            
            // Add indexes
            $table->index('mobile');
            $table->index(['subscription_type', 'subscription_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
            $table->dropIndex(['subscription_type', 'subscription_expires_at']);
            
            $table->dropColumn([
                'mobile',
                'google_id', 
                'avatar',
                'subscription_type',
                'subscription_expires_at',
                'daily_ai_quota',
                'daily_ai_used',
                'last_quota_reset'
            ]);
        });
    }
};
