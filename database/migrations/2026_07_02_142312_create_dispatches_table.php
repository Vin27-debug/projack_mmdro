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
        Schema::create('dispatches', function (Blueprint $table) {

            $table->id();

            $table->foreignId('incident_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('driver_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('ambulances')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'assigned',
                'accepted',
                'en_route',
                'arrived',
                'completed',
                'closed',
                'cancelled'
            ])->default('pending');

            $table->timestamp('assigned_at')->nullable();

            $table->timestamp('accepted_at')->nullable();

            $table->timestamp('arrived_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
