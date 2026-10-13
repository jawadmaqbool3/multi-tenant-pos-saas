<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingSignupsTable extends Migration
{
    /**
     * Pre-checkout self-signups (email + password before payment).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_signups', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('plan_id')->nullable();
            $table->unsignedInteger('price_id')->nullable();
            $table->string('email');
            $table->enum('status', [
                'pending', 'checkout_started', 'paid', 'provisioned', 'failed', 'expired',
            ])->default('pending');
            $table->string('checkout_session_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('plan_id')->references('id')->on('billing_plans')
                ->onDelete('set null')->onUpdate('cascade');
            $table->foreign('price_id')->references('id')->on('billing_prices')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_signups');
    }
}