<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [

        'user_id',
        'title',
        'message',
        'type',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo(Builder $query, ?int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId): void {
            $query->whereNull('user_id')
                ->orWhere('user_id', $userId);
        });
    }
}
