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
            $table->string('staff_id')->nullable()->after('email');
            $table->string('phone')->nullable()->after('staff_id');
            $table->string('department')->nullable()->after('role');
            $table->string('job_title')->nullable()->after('department');
            $table->string('status')->default('active')->after('job_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['staff_id', 'phone', 'department', 'job_title', 'status']);
        });
    }
};
