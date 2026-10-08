<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 25);
            $table->unsignedBigInteger('inv_voucher_id');
            $table->string('inv_voucher_type')->default('voucher');
            $table->unsignedBigInteger('pay_voucher_id')->nullable();
            $table->string('pay_voucher_type')->nullable()->default('voucher');
            $table->enum('status', [
                'generated',
                'cancelled',
                'paid',
                'refunded',
            ]);
            $table->unsignedInteger('company_id');
            $table->enum('payment_method', [
                'cash',
                'card'
            ])->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->date('date');
            $table->date('due_date');
            $table->uuid('uid');
            $table->timestamps();
            $table->string('reference')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
