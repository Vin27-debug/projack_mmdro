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
        if (!Schema::hasTable('ambulances')) {
            return;
        }

        Schema::table('ambulances', function ($table) {
            if (!Schema::hasColumn('ambulances', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('ambulances', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('ambulances')) {
            return;
        }

        Schema::table('ambulances', function ($table) {
            foreach (['latitude', 'longitude'] as $column) {
                if (Schema::hasColumn('ambulances', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
