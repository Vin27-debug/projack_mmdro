<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ambulances', 'vehicle_type')) {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE `ambulances` MODIFY `vehicle_type` ENUM('ambulance', 'rescue_van', 'fire_truck') NOT NULL DEFAULT 'ambulance'");
        }
    }

    public function down(): void
    {
        // No destructive rollback needed for this change.
    }
};
