<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingCustomerAndSubscriptionTables extends Migration
{
    /**
     * billing_customers (Stripe customer mapping) and billing_subscriptions
     * (mirror of the Stripe subscription; Stripe remains the source of truth).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_customers', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('company_id')->unique();
            $table->string('stripe_customer_id')->unique();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('plan_id')->nullable();
            $table->unsignedInteger('price_id')->nullable();
            $table->unsignedInteger('billing_customer_id')->nullable();
            $table->string('stripe_subscription_id')->unique();
            $table->enum('status', [
                'incomplete', 'incomplete_expired', 'trialing', 'active',
                'past_due', 'canceled', 'unpaid',
            ])->default('incomplete');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->foreign('company_id')->references('id')->on('companies')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('price_id')->references('id')->on('billing_prices')
                ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('billing_customer_id')->references('id')->on('billing_customers')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_subscriptions');
        Schema::dropIfExists('billing_customers');
    }
}