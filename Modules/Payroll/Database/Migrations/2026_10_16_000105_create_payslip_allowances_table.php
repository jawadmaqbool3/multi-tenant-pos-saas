<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipAllowancesTable extends Migration
{
    public function up()
    {
        Schema::create('payslip_allowances', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('payslip_id');
            $table->unsignedInteger('allowance_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('payslip_id')->references('id')->on('payslips')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('allowance_id')->references('id')->on('allowances')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['payslip_id', 'allowance_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslip_allowances');
    }
}