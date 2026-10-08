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
        $super_admin = User::create([
            'name' => 'Jawad Maqbool',
            'uid' => Helper::_uid(),
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $super_admin->save();

        $employee = User::create([
            'name' => 'Demo User',
            'uid' => Helper::_uid(),
            'email' => 'user@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        $employee->save();
    }
}
