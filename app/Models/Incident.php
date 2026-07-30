<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_RESPONDING = 'responding';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DISPATCHED,
        self::STATUS_RESPONDING,
        self::STATUS_COMPLETED,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'incident_number',
        'reporter_name',
        'contact_number',
        'incident_type',
        'location',
        'description',
        'latitude',
        'longitude',
        'priority',
        'status',
        'driver_id',
        'ambulance_id',
    ];

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'incident_id');
    }

    public function report()
    {
        return $this->hasOne(IncidentReport::class, 'incident_id');
    }

    public function scopeOpen(Builder $query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeReportable(Builder $query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->whereDoesntHave('report');
    }
}
