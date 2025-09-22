<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally left without default sample campuses.
        // You may optionally seed an initial school like below:
        // School::firstOrCreate(['name' => 'Main Campus'], ['address' => 'Address', 'email' => 'info@example.com']);
    }
}
