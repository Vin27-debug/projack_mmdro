<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('incidents')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            if (!Schema::hasColumn('incidents', 'call_received_at')) {
                $table->timestamp('call_received_at')->nullable();
            }

            if (!Schema::hasColumn('incidents', 'response_at')) {
                $table->timestamp('response_at')->nullable();
            }

            if (!Schema::hasColumn('incidents', 'at_scene_at')) {
                $table->timestamp('at_scene_at')->nullable();
            }

            if (!Schema::hasColumn('incidents', 'at_patient_at')) {
                $table->timestamp('at_patient_at')->nullable();
            }

            if (!Schema::hasColumn('incidents', 'depart_scene_at')) {
                $table->timestamp('depart_scene_at')->nullable();
            }

            if (!Schema::hasColumn('incidents', 'at_hospital_at')) {
                $table->timestamp('at_hospital_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('incidents')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            foreach (['call_received_at', 'response_at', 'at_scene_at', 'at_patient_at', 'depart_scene_at', 'at_hospital_at'] as $column) {
                if (Schema::hasColumn('incidents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
