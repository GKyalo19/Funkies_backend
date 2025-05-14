<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('class');
            $table->string('level');
            $table->string('category');
            $table->string('subject');
            $table->string('name');
            $table->string('venue');
            $table->string('description');
            $table->string('startDate');
            $table->string('endDate');
            $table->string('hosts');
            $table->string('sponsors');
            $table->integer('capacity');
            $table->softDeletes();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};




