<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    protected $fillable = ['teacher_id', 'name', 'join_code', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function students(): BelongsToMany { return $this->belongsToMany(User::class)->withTimestamps(); }
    public function activities(): BelongsToMany { return $this->belongsToMany(Activity::class)->withTimestamps(); }
}