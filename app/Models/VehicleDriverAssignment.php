<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDriverAssignment extends Model
{
    protected $fillable = [
        'driver_id',
        'ambulance_id',
        'status',
        'assigned_at',
    ];

    public static function assignDriverToAmbulance(Driver $driver, Ambulance $ambulance): self
    {
        self::where('driver_id', $driver->id)
            ->update(['status' => 'inactive']);

        return self::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class);
    }
}
