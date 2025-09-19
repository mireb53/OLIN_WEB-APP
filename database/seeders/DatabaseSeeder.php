<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash; // <-- Idagdag ito
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin12345'), // Choose a strong password!
            'role' => 'admin',
            'email_verified_at' => now(), // Pre-verify the email
        ]);
    }
}