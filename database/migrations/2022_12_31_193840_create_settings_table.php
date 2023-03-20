<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('logo')->nullable();
            $table->string('name')->nullable();
            $table->unsignedInteger('company_id')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('url')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('contact')->nullable();
            $table->boolean('maintenance_mode')->default(0);
            $table->json('asidebar');
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
        Schema::dropIfExists('settings');
    }
}
