<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::create([
        //     'name' => 'user',
        //     'username' => 'username',
        //     'type' => 'admin',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // \App\Models\User::create([
        //     'name' => 'usermanager',
        //     'username' => 'usermanager',
        //     'type' => 'manager',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // \App\Models\User::create([
        //     'name' => 'userdc',
        //     'username' => 'userdc',
        //     'type' => 'dc',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // \App\Models\User::create([
        //     'name' => 'usermarker',
        //     'username' => 'usermarker',
        //     'type' => 'marker',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // \App\Models\User::create([
        //     'name' => 'userspreading',
        //     'username' => 'userspreading',
        //     'type' => 'spreading',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // \App\Models\User::create([
        //     'name' => 'userstocker',
        //     'username' => 'userstocker',
        //     'type' => 'stocker',
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ]);

        // Generate Meja User
        // for ($i = 0;$i < 10;$i++) {
        //     \App\Models\User::create([
        //         'name' => 'meja '.sprintf("%02d", ($i+1)),
        //         'username' => 'meja_'.sprintf("%02d", ($i+1)),
        //         'type' => 'meja',
        //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //         'remember_token' => Str::random(10)
        //     ]);
        // }

        \App\Models\User::create([
            'name' => 'user_hr',
            'username' => 'user_hr',
            'type' => 'hr',
            'password' => Hash::make('123'), // password
            'remember_token' => Str::random(10),
        ]);
    }
}
