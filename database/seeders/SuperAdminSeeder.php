<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([

            'name' => 'Super Admin',

            'email' => 'admin@sis.com',

            'password' => Hash::make('password123'),

        ]);

        $user->assignRole('super_admin');
    }
}
