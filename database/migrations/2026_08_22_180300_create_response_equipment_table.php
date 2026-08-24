<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('General');
            $table->string('serial_number')->nullable()->unique();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition')->default('Good');
            $table->string('status')->default('available');
            $table->string('storage_location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_equipment');
    }
};
