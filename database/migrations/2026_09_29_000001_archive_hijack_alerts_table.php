<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hijack_alerts') && !Schema::hasTable('archived_hijack_alerts')) {
            Schema::rename('hijack_alerts', 'archived_hijack_alerts');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('archived_hijack_alerts') && !Schema::hasTable('hijack_alerts')) {
            Schema::rename('archived_hijack_alerts', 'hijack_alerts');
        }
    }
};
