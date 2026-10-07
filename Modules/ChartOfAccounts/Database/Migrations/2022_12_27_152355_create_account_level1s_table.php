<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountLevel1sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_level1s', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->enum('name', [
                'Asset',
                'Liability',
                'Equity',
                'Revenue',
                'Expense',
            ]);
            $table->unsignedInteger('company_id')->nullable();
            $table->enum('status', [
                'active',
                'inactive',
            ]);
            $table->enum('start_code', [
                '000',
                '100',
                '200',
                '300',
                '400',
            ]);
            $table->enum('end_code', [
                '099',
                '199',
                '299',
                '399',
                '499',
            ]);
            $table->timestamps();
            $table->uuid('uid');
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
        Schema::dropIfExists('account_level1s');
    }
}
