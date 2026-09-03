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

    public const INCIDENT_TYPES = [
        'Medical Emergency',
        'Accident / Collision',
        'Fire',
        'Crime / Security',
        'Natural Disaster',
        'Rescue / Trapped Person',
        'Other Emergency',
    ];

    protected $fillable = [
        'incident_number',
        'reporter_name',
        'contact_number',
        'incident_type',
        'location',
        'house_number',
        'street',
        'barangay',
        'city',
        'province',
        'description',
        'latitude',
        'longitude',
        'priority',
        'status',
        'driver_id',
        'ambulance_id',
        'call_received_at',
        'response_at',
        'at_scene_at',
        'at_patient_at',
        'depart_scene_at',
        'at_hospital_at',
        'archived_at',
        'archived_by',
        'completed_at',
        'closed_at',
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

    protected function casts(): array
    {
        return [
            'call_received_at' => 'datetime',
            'response_at' => 'datetime',
            'at_scene_at' => 'datetime',
            'at_patient_at' => 'datetime',
            'depart_scene_at' => 'datetime',
            'at_hospital_at' => 'datetime',
            'archived_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'completed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function attachments()
    {
        return $this->hasMany(IncidentAttachment::class);
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function report()
    {
        return $this->hasOne(IncidentReport::class, 'incident_id');
    }

    public function scopeNotArchived(Builder $query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query)
    {
        return $query->whereNotNull('archived_at');
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
