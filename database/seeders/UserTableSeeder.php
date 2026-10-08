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
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Jawad Maqbool',
                'uid' => Helper::_uid(),
                'password' => Hash::make('12345678'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Demo User',
                'uid' => Helper::_uid(),
                'password' => Hash::make('12345678'),
            ]
        );
    }
}
