<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            
            // Add timestamp for when the mood was entered
            $table->timestamp('entered_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
            
            // Drop the timestamp column
            $table->dropColumn('entered_at');
        });
    }
};
