<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'subject_id',
        'difficulty_level_id',
        'type',
        'kind',
        'question',
        'options',
        'answer',
        'data',
        'speak',
        'speak_lang',
        'order_num',
        'active',
    ];

    protected $casts = [
        'options' => 'array',
        'data' => 'array',
        'active' => 'boolean',
        'order_num' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class);
    }
}