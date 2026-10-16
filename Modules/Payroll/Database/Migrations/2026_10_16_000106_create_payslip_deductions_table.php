<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipDeductionsTable extends Migration
{
    public function up()
    {
        Schema::create('payslip_deductions', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('payslip_id');
            $table->unsignedInteger('deduction_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('payslip_id')->references('id')->on('payslips')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('deduction_id')->references('id')->on('deductions')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['payslip_id', 'deduction_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslip_deductions');
    }
}