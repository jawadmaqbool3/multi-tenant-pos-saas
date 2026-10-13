<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingInvoiceAndPaymentTables extends Migration
{
    /**
     * billing_invoices and billing_payments mirror Stripe records for the
     * platform/tenant read-only UIs.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->string('stripe_invoice_id')->unique();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('subscription_id')->nullable();
            $table->unsignedInteger('amount_due')->default(0);
            $table->unsignedInteger('amount_paid')->default(0);
            $table->string('currency', 3)->default('usd');
            $table->enum('status', ['draft', 'open', 'paid', 'void', 'uncollectible'])->default('draft');
            $table->string('billing_reason')->nullable();
            $table->string('hosted_invoice_url')->nullable();
            $table->string('invoice_pdf')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->foreign('company_id')->references('id')->on('companies')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('subscription_id')->references('id')->on('billing_subscriptions')
                ->onDelete('set null')->onUpdate('cascade');
        });

        Schema::create('billing_payments', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->string('stripe_payment_intent_id')->unique();
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('invoice_id')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->string('currency', 3)->default('usd');
            $table->enum('status', [
                'requires_payment_method', 'requires_action', 'processing',
                'succeeded', 'canceled', 'failed',
            ])->default('requires_payment_method');
            $table->timestamps();

            $table->index('company_id');
            $table->foreign('company_id')->references('id')->on('companies')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('invoice_id')->references('id')->on('billing_invoices')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_payments');
        Schema::dropIfExists('billing_invoices');
    }
}