<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('name');
            $table->unsignedInteger('company_id');
            $table->string('code')->unique();
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('tags')->nullable();
            $table->text('meta_description')->nullable();
            $table->datetime('published_date')->nullable();
            $table->double('unit_price')->default(0);
            $table->double('purchase_price')->default(0);
            $table->integer('stock')->default(0);
            $table->json('images')->nullable();
            $table->string('slug')->default('not-found');
            $table->enum('status', ['published', 'scheduled', 'unpublished'])->nullable();
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
        Schema::dropIfExists('products');
    }
}
