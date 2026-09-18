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
            $table->string('contact_number')->nullable()->change();
            $table->string('location')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('incidents')) {
            return;
        }

        Schema::table('incidents', function (Blueprint $table): void {
            $table->string('contact_number')->nullable(false)->change();
            $table->string('location')->nullable(false)->change();
        });
    }
};
