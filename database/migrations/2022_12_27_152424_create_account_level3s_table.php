<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountLevel3sTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_level3s', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name');
            $table->unsignedInteger('parent_id');
            $table->string('code', 20);
            $table->enum('status', [
                'active',
                'inactive',
            ]);
            $table->unsignedInteger('company_id')->nullable();
            $table->timestamps();
            $table->uuid('uid');
            $table->foreign('parent_id')
                ->references('id')
                ->on('account_level2s')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        Schema::dropIfExists('account_level3s');
    }
}
