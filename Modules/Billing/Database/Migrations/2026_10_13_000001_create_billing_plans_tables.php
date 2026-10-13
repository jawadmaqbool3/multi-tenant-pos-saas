<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPlansTables extends Migration
{
    /**
     * billing_plans (the catalog, synced to Stripe Products) and
     * billing_prices (synced to Stripe Prices).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('stripe_product_id')->nullable()->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_public')->default(false);
            $table->unsignedTinyInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('billing_prices', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('plan_id');
            $table->string('stripe_price_id')->nullable()->unique();
            $table->enum('interval', ['month', 'year'])->default('month');
            $table->unsignedTinyInteger('interval_count')->default(1);
            $table->unsignedInteger('unit_amount');
            $table->string('currency', 3)->default('usd');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_prices');
        Schema::dropIfExists('billing_plans');
    }
}