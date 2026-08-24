<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseEquipment extends Model
{
    protected $fillable = [
        'name',
        'category',
        'serial_number',
        'quantity',
        'condition',
        'status',
        'storage_location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }
}
