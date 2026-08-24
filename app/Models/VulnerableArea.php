<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VulnerableArea extends Model
{
    protected $fillable = [
        'name',
        'area_type',
        'address',
        'household_count',
        'population_count',
        'vulnerability_level',
        'latitude',
        'longitude',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'household_count' => 'integer',
            'population_count' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
