<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vulnerable_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('area_type')->default('Household Cluster');
            $table->string('address');
            $table->unsignedInteger('household_count')->default(0);
            $table->unsignedInteger('population_count')->default(0);
            $table->string('vulnerability_level')->default('Medium');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['status', 'vulnerability_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vulnerable_areas');
    }
};
