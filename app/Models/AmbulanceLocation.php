<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmbulanceLocation extends Model
{
    protected $fillable = [
        'ambulance_id',
        'latitude',
        'longitude',
    ];
}
