<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EN_ROUTE = 'en_route';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ASSIGNED,
        self::STATUS_ACCEPTED,
        self::STATUS_EN_ROUTE,
        self::STATUS_ARRIVED,
        self::STATUS_COMPLETED,
        self::STATUS_CLOSED,
        self::STATUS_CANCELLED,
    ];

    public static function validStatuses(): array
    {
        return self::VALID_STATUSES;
    }

    protected $fillable = [
        'incident_id',
        'driver_id',
        'vehicle_id',
        'status',
        'assigned_at',
        'accepted_at',
        'arrived_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function ambulance()
    {
        return $this->belongsTo(Ambulance::class, 'vehicle_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Ambulance::class, 'vehicle_id');
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeInProgress(Builder $query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_ASSIGNED,
            self::STATUS_ACCEPTED,
            self::STATUS_EN_ROUTE,
            self::STATUS_ARRIVED,
        ]);
    }

    public function scopeReportable(Builder $query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->whereDoesntHave('incident.report');
    }
}
