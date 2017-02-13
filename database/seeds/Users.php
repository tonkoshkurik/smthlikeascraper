<?php

use Illuminate\Database\Seeder;
use App\User;


class Users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create new User with name - Admin
        $admin = new User();
        $admin->name = 'Admin';
        $admin->email = 'admin@admin.com';
        $admin->password = bcrypt('123456');
        $admin->save();
    }
}
