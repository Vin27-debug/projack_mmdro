<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('incidents') || Schema::hasColumn('incidents', 'return_to_base_at')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            $table->timestamp('return_to_base_at')->nullable()->after('at_hospital_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('incidents') || !Schema::hasColumn('incidents', 'return_to_base_at')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            $table->dropColumn('return_to_base_at');
        });
    }
};
