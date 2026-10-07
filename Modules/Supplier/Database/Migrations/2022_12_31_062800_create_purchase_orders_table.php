<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 20);
            $table->unsignedInteger('company_id');
            $table->unsignedDouble('amount')->default(0);
            $table->unsignedInteger('supplier_id');
            $table->unsignedBigInteger('voucher_id');
            $table->unsignedInteger('user_id');
            $table->enum('status', [
                'generated',
                'refunded'
            ])->default('generated');
            $table->string('reference')->nullable();
            $table->foreign('supplier_id')->on('suppliers')->references('id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('voucher_id')->on('vouchers')->references('id')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->on('users')->references('id')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('uid');
            $table->timestamps();
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
        Schema::dropIfExists('purchase_orders');
    }
}
