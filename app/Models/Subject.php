<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'accent',
        'accent_dark',
        'bg',
        'type',
        'price',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'integer',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}