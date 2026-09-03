<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsLocation extends Model
{
    protected $fillable = [
        'driver_id',
        'latitude',
        'longitude',
        'recorded_at',
        'speed_kmh',
        'speed_status',
        'speed_limit_kmh',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'recorded_at' => 'datetime',
        'speed_kmh' => 'float',
        'speed_limit_kmh' => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
