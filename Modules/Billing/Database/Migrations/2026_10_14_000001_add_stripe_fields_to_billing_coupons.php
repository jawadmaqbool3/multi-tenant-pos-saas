<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeFieldsToBillingCoupons extends Migration
{
    /**
     * Add the Stripe Coupon / Promotion Code mapping plus an optional
     * expiry to the local coupon catalog.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_coupons', function (Blueprint $table) {
            $table->string('stripe_coupon_id')->nullable()->after('code');
            $table->string('stripe_promotion_code_id')->nullable()->after('stripe_coupon_id');
            $table->timestamp('expires_at')->nullable()->after('max_redemptions');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('billing_coupons', function (Blueprint $table) {
            $table->dropColumn(['stripe_coupon_id', 'stripe_promotion_code_id', 'expires_at']);
        });
    }
}
