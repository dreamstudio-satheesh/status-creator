<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Font extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'family',
        'file_path',
        'is_tamil',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_tamil' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTamil($query)
    {
        return $query->where('is_tamil', true);
    }

    public function scopeEnglish($query)
    {
        return $query->where('is_tamil', false);
    }
}
