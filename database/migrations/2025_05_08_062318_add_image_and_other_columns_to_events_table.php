<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('poster')->nullable();
            $table->integer('registration_fee');
            $table->string('county');
            $table->string('link')->nullable();
            $table->string('currency');
            $table->string('participation_mode');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('poster');
            $table->dropColumn('registration_fee');
            $table->dropColumn('county');
            $table->dropColumn('link');
            $table->dropColumn('currency');
            $table->dropColumn('participation_mode');
        });
    }
};
