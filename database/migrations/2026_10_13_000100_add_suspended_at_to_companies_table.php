<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuspendedAtToCompaniesTable extends Migration
{
    /**
     * Adds the platform "suspend" flag. A non-null value blocks all tenant
     * users in the access gate while the Stripe subscription stays intact.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('status');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('suspended_at');
        });
    }
}