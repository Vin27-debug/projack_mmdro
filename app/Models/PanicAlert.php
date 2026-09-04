<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanicAlert extends Model
{
    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'resolved',
        'triggered_at',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'triggered_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
