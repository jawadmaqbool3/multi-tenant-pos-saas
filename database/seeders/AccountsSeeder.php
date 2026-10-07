<?php

namespace Database\Seeders;

use App\Services\DefaultService;
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
