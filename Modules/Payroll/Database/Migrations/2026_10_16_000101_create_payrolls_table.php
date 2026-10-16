<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('company_id');
            $table->date('period');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->unsignedInteger('total_employees')->default(0);
            $table->decimal('total_base', 12, 2)->default(0);
            $table->decimal('total_allowances', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('voucher_id')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unique(['company_id', 'period']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}