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
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
