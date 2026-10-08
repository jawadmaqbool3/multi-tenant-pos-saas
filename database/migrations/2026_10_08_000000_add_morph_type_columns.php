<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMorphTypeColumns extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('creator_type')->default('user')->after('created_by');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('inv_voucher_type')->default('voucher')->after('inv_voucher_id');
            $table->string('pay_voucher_type')->nullable()->default('voucher')->after('pay_voucher_id');
        });
        Schema::table('order_users', function (Blueprint $table) {
            $table->string('user_type')->default('user')->after('user_id');
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('product_type')->default('product')->after('product_id');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('voucher_type')->default('voucher')->after('voucher_id');
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('creator_type');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['inv_voucher_type', 'pay_voucher_type']);
        });
        Schema::table('order_users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('voucher_type');
        });
    }
}
