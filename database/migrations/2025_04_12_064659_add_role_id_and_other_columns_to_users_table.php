//Adding columns to users table

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('is_active')->default(true);
            $table->foreignId('role_id')->constrained('roles');
            $table->string('user_photo')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->dropColumn('user_photo');
            $table->dropSoftDeletes();
        });
    }
};
