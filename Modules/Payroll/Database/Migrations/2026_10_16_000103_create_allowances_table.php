<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllowancesTable extends Migration
{
    public function up()
    {
        Schema::create('allowances', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->uuid('uid');
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['company_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('allowances');
    }
}