<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifficultyLevel extends Model
{
    protected $fillable = ['name', 'slug', 'min_age', 'max_age', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}