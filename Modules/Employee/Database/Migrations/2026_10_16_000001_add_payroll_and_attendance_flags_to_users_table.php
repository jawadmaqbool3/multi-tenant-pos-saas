<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayrollAndAttendanceFlagsToUsersTable extends Migration
{
    /**
     * Add the payroll / attendance toggles to users. No backfill: existing
     * users start with both flags off (both default false).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('payroll_enabled')->default(false)->after('status');
            $table->boolean('attendance_enabled')->default(false)->after('payroll_enabled');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payroll_enabled', 'attendance_enabled']);
        });
    }
}
