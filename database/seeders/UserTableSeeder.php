<?php

namespace Database\Seeders;

use App\Core\Helper;
use App\Support\DefaultAccounts;
use Modules\Employee\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (DefaultAccounts::all() as $email => $name) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'uid' => Helper::_uid(),
                    'password' => Hash::make(DefaultAccounts::PASSWORD),
                    'status' => 'active',
                ]
            );
        }
    }
}
