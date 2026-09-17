<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('related_id')->nullable()->after('type');
            $table->string('related_type')->nullable()->after('related_id');
            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['related_type', 'related_id']);
            $table->dropColumn(['related_id', 'related_type']);
        });
    }
};
