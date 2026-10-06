<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ambulance extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ON_DUTY = 'on_duty';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const VALID_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_ON_DUTY,
        self::STATUS_MAINTENANCE,
    ];

    protected $fillable = [
        'plate_number',
        'vehicle_name',
        'vehicle_type',
        'status',
        'latitude',
        'longitude',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function maintenances()
    {
        return $this->hasMany(
            VehicleMaintenance::class
        );
    }

    public function dispatches()
    {
        return $this->hasMany(
            Dispatch::class,
            'vehicle_id'
        );
    }

    public function driverAssignments()
    {
        return $this->hasMany(VehicleDriverAssignment::class);
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->notArchived()
            ->where('status', self::STATUS_AVAILABLE)
            ->whereDoesntHave('dispatches', fn(Builder $dispatches) => $dispatches->whereNotIn('status', [
                Dispatch::STATUS_COMPLETED,
                Dispatch::STATUS_CLOSED,
                Dispatch::STATUS_CANCELLED,
            ]));
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }
}
