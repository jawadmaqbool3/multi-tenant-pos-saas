<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingCouponTables extends Migration
{
    /**
     * billing_coupons (admin catalog) and billing_redemptions (which company
     * redeemed which coupon on which subscription).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_coupons', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->unsignedInteger('percent_off')->nullable();
            $table->unsignedInteger('amount_off')->nullable();
            $table->enum('duration', ['once', 'repeating', 'forever'])->default('once');
            $table->unsignedTinyInteger('duration_in_months')->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('billing_redemptions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('subscription_id')->nullable();
            $table->string('stripe_promotion_code_id')->nullable();
            $table->timestamps();

            $table->index(['coupon_id', 'company_id']);
            $table->foreign('coupon_id')->references('id')->on('billing_coupons')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_redemptions');
        Schema::dropIfExists('billing_coupons');
    }
}