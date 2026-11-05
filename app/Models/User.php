<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function plans()
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        'points' => 'integer',
        'level' => 'integer',
    ];
    }

    /**
     * Recalculate the user's level based on accumulated points.
     * Uses custom thresholds:
     * Level 1: 0-99 points
     * Level 2: 100-249 points
     * Level 3: 250-499 points
     * Level 4: 500-999 points
     * Level 5: 1000+ points
     */
    public function recalculateLevel(): void
    {
        $points = $this->points ?? 0;

        if ($points >= 1000) {
            $this->level = 5;
        } elseif ($points >= 500) {
            $this->level = 4;
        } elseif ($points >= 250) {
            $this->level = 3;
        } elseif ($points >= 100) {
            $this->level = 2;
        } else {
            $this->level = 1;
        }

        $this->save();
    }
}
