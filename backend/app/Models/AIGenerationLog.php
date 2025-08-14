<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIGenerationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'prompt',
        'response',
        'model_used',
        'tokens_used',
        'cost',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:6',
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

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
