<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $table = 'incident_reports';

    protected $fillable = [
        'incident_id',
        'driver_id',
        'summary',
        'actions_taken',
        'casualties',
        'remarks',
        'submitted_at',
        'status',
    ];


    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
