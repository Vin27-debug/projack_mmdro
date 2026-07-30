<?php

namespace App\Models;

use App\Models\Driver;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Notification;

#[Fillable([
    'name',
    'email',
    'password',
    'badge_id',
    'status',
    'created_by',
    'approved_by',
    'approved_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /**@use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
        ];
    }
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function notifications()
    {
        return $this->hasMany(
            Notification::class
        );
    }
}
