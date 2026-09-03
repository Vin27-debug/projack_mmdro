<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incidents')) {
            Schema::table('incidents', function (Blueprint $table): void {
                if (!Schema::hasColumn('incidents', 'house_number')) $table->string('house_number')->nullable();
                if (!Schema::hasColumn('incidents', 'street')) $table->string('street')->nullable();
                if (!Schema::hasColumn('incidents', 'barangay')) $table->string('barangay')->nullable();
                if (!Schema::hasColumn('incidents', 'city')) $table->string('city')->nullable();
                if (!Schema::hasColumn('incidents', 'province')) $table->string('province')->nullable();
                if (!Schema::hasColumn('incidents', 'latitude')) $table->decimal('latitude', 10, 7)->nullable();
                if (!Schema::hasColumn('incidents', 'longitude')) $table->decimal('longitude', 10, 7)->nullable();
                if (!Schema::hasColumn('incidents', 'completed_at')) $table->timestamp('completed_at')->nullable();
                if (!Schema::hasColumn('incidents', 'closed_at')) $table->timestamp('closed_at')->nullable();
            });
        }

        if (Schema::hasTable('gps_locations')) {
            Schema::table('gps_locations', function (Blueprint $table): void {
                if (!Schema::hasColumn('gps_locations', 'speed_kmh')) $table->decimal('speed_kmh', 6, 2)->nullable();
                if (!Schema::hasColumn('gps_locations', 'speed_status')) $table->string('speed_status', 20)->nullable();
                if (!Schema::hasColumn('gps_locations', 'speed_limit_kmh')) $table->decimal('speed_limit_kmh', 6, 2)->nullable();
            });
        }

        if (Schema::hasTable('dispatches')) {
            Schema::table('dispatches', function (Blueprint $table): void {
                if (!Schema::hasColumn('dispatches', 'en_route_at')) $table->timestamp('en_route_at')->nullable();
                if (!Schema::hasColumn('dispatches', 'declined_at')) $table->timestamp('declined_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['incidents' => ['house_number', 'street', 'barangay', 'city', 'province', 'completed_at', 'closed_at'], 'gps_locations' => ['speed_kmh', 'speed_status', 'speed_limit_kmh'], 'dispatches' => ['en_route_at', 'declined_at']] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
                }
            });
        }
    }
};
