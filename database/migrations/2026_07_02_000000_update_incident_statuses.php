<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE incidents MODIFY status ENUM('pending', 'dispatched', 'en_route', 'on_scene', 'completed', 'cancelled') DEFAULT 'pending'");
        }
        // For SQLite (if needed)
        else {
            // SQLite doesn't support ENUM, so we'll just keep it as a string
            // The validation will happen in the application
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE incidents MODIFY status ENUM('pending', 'dispatched', 'responding', 'completed', 'cancelled') DEFAULT 'pending'");
        }
    }
};
