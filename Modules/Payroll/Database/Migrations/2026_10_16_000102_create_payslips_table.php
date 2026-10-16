<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('payroll_id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('company_id');
            $table->decimal('base_salary', 12, 2);
            $table->decimal('total_allowances', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('payroll_id')->references('id')->on('payrolls')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['payroll_id', 'employee_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}