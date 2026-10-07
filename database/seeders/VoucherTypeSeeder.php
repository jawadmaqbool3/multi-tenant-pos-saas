<?php

namespace Database\Seeders;

use App\Services\DefaultService;
use Modules\ChartOfAccounts\Models\VoucherType;
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
        $defaultService = app(DefaultService::class);
        $defaultService->initiateVouchers();
    }
}
