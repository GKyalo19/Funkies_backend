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
        Schema::dropIfExists('likes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events');
            $table->timestamps();
        });
    }
};



// use Illuminate\Database\Migrations\Migration;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;

// return new class extends Migration
// {
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::table('events', function (Blueprint $table) {
    //         $table->dateTime('startDate')->change();
    //         $table->dateTime('endDate')->change();
    //     });
    // }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::table('events', function (Blueprint $table) {
    //         $table->date('startDate')->change();
    //         $table->date('endDate')->change();
    //     });
    // }
// };
