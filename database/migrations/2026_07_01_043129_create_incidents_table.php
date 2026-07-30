<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('incident_number')->unique();

            $table->string('reporter_name');

            $table->string('contact_number');

            $table->string('incident_type');

            $table->string('location');

            $table->text('description')->nullable();

            $table->enum('status', [
                'pending',
                'dispatched',
                'responding',
                'completed',
                'closed',
                'cancelled'
            ])->default('pending');

            $table->foreignId('ambulance_id')
                ->nullable()
                ->constrained('ambulances')
                ->nullOnDelete();

            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
