<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'google_id',
        'avatar',
        'subscription_type',
        'subscription_expires_at',
        'daily_ai_quota',
        'daily_ai_used',
        'last_quota_reset',
        'preferences',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'last_quota_reset' => 'date',
            'preferences' => 'array',
        ];
    }

    /**
     * Check if user is premium
     */
    public function isPremium(): bool
    {
        return $this->subscription_type === 'premium' && 
               $this->subscription_expires_at && 
               $this->subscription_expires_at->isFuture();
    }

    /**
     * Check if user can generate AI content
     */
    public function canGenerateAI(): bool
    {
        $this->resetDailyQuotaIfNeeded();
        return $this->daily_ai_used < $this->daily_ai_quota;
    }

    /**
     * Reset daily quota if needed
     */
    public function resetDailyQuotaIfNeeded(): void
    {
        if (!$this->last_quota_reset || $this->last_quota_reset->isYesterday()) {
            $this->update([
                'daily_ai_used' => 0,
                'last_quota_reset' => now()->toDateString(),
            ]);
        }
    }

    /**
     * Increment AI usage
     */
    public function incrementAIUsage(): void
    {
        $this->increment('daily_ai_used');
    }

    // Relationships
    public function creations()
    {
        return $this->hasMany(UserCreation::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function feedback()
    {
        return $this->hasMany(UserFeedback::class);
    }

    public function aiGenerationLogs()
    {
        return $this->hasMany(AIGenerationLog::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }
}
