<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_moods', function (Blueprint $table) {
            // Drop the unique constraint to allow multiple moods per day
            $table->dropUnique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_moods', function (Blueprint $table) {
            // Re-add the unique constraint
            $table->unique(['user_id', 'date']);
        });
    }
};
