<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_EN_ROUTE = 'en_route';
    public const STATUS_ON_SCENE = 'on_scene';
    public const STATUS_RETURNING = 'returning';
    public const STATUS_OFFLINE = 'offline';

    public const VALID_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_ASSIGNED,
        self::STATUS_EN_ROUTE,
        self::STATUS_ON_SCENE,
        self::STATUS_RETURNING,
        self::STATUS_OFFLINE,
    ];

    protected $fillable = [
        'user_id',
        'badge_id',
        'contact_number',
        'license_number',
        'license_expiry',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gpsLocations()
    {
        return $this->hasMany(GpsLocation::class);
    }

    public function report()
    {
        return $this->hasOne(IncidentReport::class);
    }

    public function reports()
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function incidentReports()
    {
        return $this->hasMany(
            IncidentReport::class,
            'driver_id'
        );
    }

    public function dispatches()
    {
        return $this->hasMany(
            Dispatch::class,
            'driver_id'
        );
    }

    public function activeDispatch()
    {
        return $this->hasOne(Dispatch::class, 'driver_id')
            ->whereIn('status', [
                Dispatch::STATUS_PENDING,
                Dispatch::STATUS_ASSIGNED,
                Dispatch::STATUS_ACCEPTED,
                Dispatch::STATUS_EN_ROUTE,
                Dispatch::STATUS_ARRIVED,
            ])
            ->latest('created_at');
    }

    public function hasOpenDispatch(): bool
    {
        return $this->dispatches()
            ->whereNotIn('status', [
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ])
            ->exists();
    }

    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleDriverAssignment::class);
    }

    public function activeVehicleAssignment()
    {
        return $this->hasOne(VehicleDriverAssignment::class)
            ->where('status', 'active')
            ->latest('assigned_at');
    }
}
