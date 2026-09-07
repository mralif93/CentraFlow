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
            $table->boolean('hrms_access')->default(true)->after('status');
            $table->string('hrms_role')->nullable()->after('hrms_access');
            $table->boolean('payroll_access')->default(true)->after('hrms_role');
            $table->string('payroll_role')->nullable()->after('payroll_access');
            $table->boolean('clinic_access')->default(true)->after('payroll_role');
            $table->string('clinic_role')->nullable()->after('clinic_access');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hrms_access',
                'hrms_role',
                'payroll_access',
                'payroll_role',
                'clinic_access',
                'clinic_role',
            ]);
        });
    }
};
