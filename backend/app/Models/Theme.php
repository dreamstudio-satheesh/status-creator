<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'name_ta',
        'description',
        'icon',
        'color',
        'is_active',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function templates()
    {
        return $this->hasMany(Template::class);
    }

    public function activeTemplates()
    {
        return $this->hasMany(Template::class)->where('is_active', true);
    }

    public function featuredTemplates()
    {
        return $this->hasMany(Template::class)->where('is_featured', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return app()->getLocale() === 'ta' ? $this->name_ta : $this->name;
    }
}
