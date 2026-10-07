<?php

namespace Database\Seeders;

use App\Services\DefaultService;
use App\Models\AccountLevel1;
use App\Models\AccountLevel2;
use App\Models\AccountLevel3;
use App\Models\AccountLevel4;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaults = app(DefaultService::class);
        $defaults->initiateAccounts();
        $defaults->initiateVouchers();
        $defaults->initiateAccountSettings();
    }
}
