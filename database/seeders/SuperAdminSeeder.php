<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin without any school assignment
        // Super Admin will create schools through the interface
        User::firstOrCreate(
            ['email' => 'superadmin@olin.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperSecure123!'),
                'role' => 'super_admin',
                'school_id' => null, // No school assignment for Super Admin
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Note: No automatic school or school admin creation
        // Super Admin must create schools and assign school admins through the interface
        
        echo "Super Admin created successfully!\n";
        echo "Email: superadmin@olin.test\n";
        echo "Password: SuperSecure123!\n";
        echo "Please login and create your first school in Settings.\n";
    }
}
