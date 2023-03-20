<?php

namespace Database\Seeders;

use App\Http\Controllers\DefaultController;
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
        $defaults = new DefaultController();
        $defaults->initiateAccounts();
        $defaults->initiateVouchers();
        $defaults->initiateAccountSettings();
    }
}
