<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPlatformToPermissionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->boolean('is_platform')->default(false)->after('company_id');
        });

        Schema::table('permission_modules', function (Blueprint $table) {
            $table->boolean('is_platform')->default(false)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('is_platform');
        });

        Schema::table('permission_modules', function (Blueprint $table) {
            $table->dropColumn('is_platform');
        });
    }
}
