<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique();
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('office')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);

            $table->dropColumn([
                'employee_id',
                'position',
                'department',
                'contact_number',
                'office',
            ]);
        });
    }
};