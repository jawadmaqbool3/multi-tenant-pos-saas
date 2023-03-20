<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 25);
            $table->unsignedInteger('voucher_type_id');
            $table->date('date');
            $table->double('amount')->default(0);
            $table->enum('type', [
                'single',
                'multiple',
                'void'
            ])->default('single');
            $table->text('description')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('voucher_type_id')->references('id')->on('voucher_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->uuid('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
}
