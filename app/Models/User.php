<?php

namespace App\Models;

use App\Models\Driver;
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
    'employee_id',
    'position',
    'department',
    'contact_number',
    'office',
    'badge_id',
    'status',
    'created_by',
    'approved_by',
    'approved_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'approved_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(self::class, 'created_by');
    }
}
