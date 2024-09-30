<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('@SuAdmin777'),
        ]);
        $superAdmin->assignRole('super_admin');

        // IT Admin
        $itAdmin = User::create([
            'name' => 'IT Admin',
            'username' => 'itadmin',
            'email' => 'itadmin@example.com',
            'password' => bcrypt('@ITadmin999'),
        ]);
        $itAdmin->assignRole('it_admin');

        // Regular User
        $regularUser = User::create([
            'name' => 'Regular Admin',
            'username' => 'regularuser',
            'email' => 'regularuser@example.com',
            'password' => bcrypt('password'),
        ]);
        $regularUser->assignRole('user');

        // Therapist
        $therapist = User::create([
            'name' => 'Therapist Admin',
            'username' => 'therapist',
            'email' => 'therapist@example.com',
            'password' => bcrypt('password'),
        ]);
        $therapist->assignRole('therapist');
    }
}
