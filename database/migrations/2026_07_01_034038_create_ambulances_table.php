<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ambulances', function (Blueprint $table) {
        $table->id();

        $table->string('plate_number')->unique();

        $table->string('vehicle_name');

        $table->enum('vehicle_type', [
            'ambulance',
            'rescue_van',
            'fire_truck'
        ])->default('ambulance');

        $table->enum('status', [
            'available',
            'on_duty',
            'maintenance'
        ])->default('available');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambulances');
    }
};
