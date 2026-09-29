<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
protected $fillable = [
    'name',
    'age',
    'email',
    'parent_email',
    'parent_id',
    'school',
    'gender',
    'country',
    'state',
    'terms_accepted_at',
    'password',
    'is_admin',
    'role',
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'parent_email',
        'terms_accepted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'age' => 'integer',
            'terms_accepted_at' => 'datetime',
            'role' => 'string',
        ];
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }
    public function parent(): BelongsTo
{
    return $this->belongsTo(User::class, 'parent_id');
}

public function children(): HasMany
{
    return $this->hasMany(User::class, 'parent_id');
}

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function enrolledClassrooms(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)->withTimestamps();
    }
}
