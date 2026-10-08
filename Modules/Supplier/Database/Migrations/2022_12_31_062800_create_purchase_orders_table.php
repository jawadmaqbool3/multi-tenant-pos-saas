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
            $table->string('voucher_type')->default('voucher');
            $table->unsignedInteger('user_id');
            $table->string('user_type')->default('user');
            $table->enum('status', [
                'generated',
                'refunded'
            ])->default('generated');
            $table->string('reference')->nullable();
            $table->foreign('supplier_id')->on('suppliers')->references('id')->onDelete('cascade')->onUpdate('cascade');
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
