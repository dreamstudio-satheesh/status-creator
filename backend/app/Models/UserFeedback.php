<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    use HasFactory;

    protected $table = 'user_feedback';

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'message',
        'rating',
        'status',
        'admin_response',
        'responded_at',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'metadata' => 'array',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}