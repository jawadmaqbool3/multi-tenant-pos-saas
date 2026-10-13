<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPlanBundleTables extends Migration
{
    /**
     * Plan = a bundle of feature modules (permission_modules), explicit
     * permissions and resource limits.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_plan_features', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('permission_module_id');
            $table->timestamps();

            $table->unique(['plan_id', 'permission_module_id'], 'billing_plan_features_bundle_unique');
            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('permission_module_id')->references('id')->on('permission_modules')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('billing_plan_permissions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('plan_id');
            $table->unsignedInteger('permission_id');
            $table->timestamps();

            $table->unique(['plan_id', 'permission_id'], 'billing_plan_permissions_unique');
            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('billing_plan_limits', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('plan_id');
            $table->string('resource_key');
            $table->integer('limit_value')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'resource_key'], 'billing_plan_limits_unique');
            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_plan_limits');
        Schema::dropIfExists('billing_plan_permissions');
        Schema::dropIfExists('billing_plan_features');
    }
}