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
        Schema::table('user_songs', function (Blueprint $table) {
            $table->string('audio_url')->nullable()->after('thumbnail');
            $table->timestamp('audio_extracted_at')->nullable()->after('audio_url');
            $table->index(['song_id', 'user_id', 'audio_extracted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_songs', function (Blueprint $table) {
            $table->dropIndex(['song_id', 'user_id', 'audio_extracted_at']);
            $table->dropColumn(['audio_url', 'audio_extracted_at']);
        });
    }
};
