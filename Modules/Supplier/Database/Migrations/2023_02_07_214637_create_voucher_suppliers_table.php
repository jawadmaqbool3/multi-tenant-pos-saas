<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_id');
            $table->string('voucher_type')->default('voucher');
            $table->unsignedInteger('supplier_id');
            $table->foreign('supplier_id')->on('suppliers')->references('id')->onDelete('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_suppliers');
    }
}
