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
          Schema::create('incident_reports', function (Blueprint $table) {

        $table->id();

        $table->foreignId('incident_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('driver_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->text('summary');

        $table->text('actions_taken');

        $table->string('casualties')
            ->nullable();

        $table->text('remarks')
            ->nullable();

        $table->timestamp('submitted_at')
            ->nullable();

        $table->string('status')
            ->default('available');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
