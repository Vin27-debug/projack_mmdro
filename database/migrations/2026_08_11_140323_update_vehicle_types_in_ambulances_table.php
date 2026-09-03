<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('PRAGMA foreign_keys=off;');

        Schema::create('ambulances_new', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('vehicle_name');

            $table->string('vehicle_type')
                ->default('ambulance');

            $table->enum('status', [
                'available',
                'on_duty',
                'maintenance'
            ])->default('available');

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->timestamps();
        });

        DB::statement('
            INSERT INTO ambulances_new
            (id, plate_number, vehicle_name, vehicle_type, status, latitude, longitude, created_at, updated_at)
            SELECT
                id, plate_number, vehicle_name, vehicle_type, status, latitude, longitude, created_at, updated_at
            FROM ambulances
        ');

        Schema::drop('ambulances');

        Schema::rename('ambulances_new', 'ambulances');

        DB::statement('PRAGMA foreign_keys=on;');
    }

    public function down(): void
    {
        // No destructive rollback needed for this change.
    }
};
