<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCreation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'image_url',
        'custom_text',
        'settings',
        'is_ai_generated',
        'shared_count',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_ai_generated' => 'boolean',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    // Methods
    public function incrementShares()
    {
        $this->increment('shared_count');
    }
}
