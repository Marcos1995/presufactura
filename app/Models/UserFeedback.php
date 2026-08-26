<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    protected $table = 'user_feedback';

    protected $fillable = [
        'user_id',
        'dismissed',
        'expected',
        'difficult',
        'used_before',
        'missing_weekly',
        'would_recommend',
    ];

    protected function casts(): array
    {
        return [
            'dismissed' => 'boolean',
            'would_recommend' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
