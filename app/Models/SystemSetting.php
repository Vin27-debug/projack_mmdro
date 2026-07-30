<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Controller;

Schema::create('system_settings', function (Blueprint $table) {

    $table->id();

    $table->string('system_name')
        ->default('MuniResQ');

    $table->string('agency_name')
        ->nullable();

    $table->string('hotline')
        ->nullable();

    $table->string('contact_number')
        ->nullable();

    $table->string('email')
        ->nullable();

    $table->string('logo')
        ->nullable();

    $table->boolean('maintenance_mode')
        ->default(false);

    $table->timestamps();
});
