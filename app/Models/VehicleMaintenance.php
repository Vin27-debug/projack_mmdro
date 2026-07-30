<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenance extends Model
{
    protected $fillable = [
        'ambulance_id',
        'maintenance_type',
        'description',
        'scheduled_date',
        'completed_date',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
    ];

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class);
    }
}
