<?php

namespace Database\Seeders;

use App\Http\Controllers\DefaultController;
use App\Models\VoucherType;
use Illuminate\Database\Seeder;

class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultController = new DefaultController();
        $defaultController->initiateVouchers();
    }
}
