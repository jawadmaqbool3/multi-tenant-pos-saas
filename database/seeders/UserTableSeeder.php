<?php

namespace Database\Seeders;

use App\Core\Helper;
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
        User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'SaaS Owner',
                'uid' => Helper::_uid(),
                'password' => Hash::make('12345678'),
                'status' => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Company Admin',
                'uid' => Helper::_uid(),
                'password' => Hash::make('12345678'),
                'status' => 'active',
            ]
        );
    }
}
