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
        Schema::table('likes', function(Blueprint $table){
            $table->unique(['user_id', 'event_id'], 'likes_user_id_event_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('likes', function(Blueprint $table){
            $table->dropUnique(['likes_user_id_event_id_unique']);
        });
    }
};
