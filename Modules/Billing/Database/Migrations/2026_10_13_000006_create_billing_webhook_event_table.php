<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingWebhookEventTable extends Migration
{
    /**
     * Idempotency ledger for incoming Stripe webhooks.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_webhook_events', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->string('stripe_event_id')->unique();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->enum('status', ['received', 'processing', 'processed', 'failed'])->default('received');
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_webhook_events');
    }
}