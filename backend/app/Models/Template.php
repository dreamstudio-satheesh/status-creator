<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'title',
        'background_image',
        'quote_text',
        'quote_text_ta',
        'font_family',
        'font_size',
        'text_color',
        'text_alignment',
        'padding',
        'is_premium',
        'is_featured',
        'is_active',
        'usage_count',
        'ai_generated',
        'image_caption',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'ai_generated' => 'boolean',
        ];
    }

    // Relationships
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function userCreations()
    {
        return $this->hasMany(UserCreation::class);
    }

    public function aiGenerationLogs()
    {
        return $this->hasMany(AIGenerationLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_premium', false);
    }

    // Methods
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function getQuoteTextAttribute($value)
    {
        return app()->getLocale() === 'ta' ? $this->quote_text_ta : $value;
    }
}
