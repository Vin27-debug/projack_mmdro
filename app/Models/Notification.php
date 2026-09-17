<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'related_id',
        'related_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedRecord()
    {
        if (blank($this->related_type) || blank($this->related_id)) {
            return null;
        }

        $modelClass = $this->related_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->related_id);
    }

    public function scopeVisibleTo(Builder $query, ?int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId): void {
            $query->whereNull('user_id')
                ->orWhere('user_id', $userId);
        });
    }
}
