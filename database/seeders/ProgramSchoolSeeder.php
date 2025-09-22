<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = \App\Models\School::all();
        $programs = \App\Models\Program::all();
        
        if ($schools->count() > 0 && $programs->count() > 0) {
            foreach ($programs as $index => $program) {
                $program->school_id = $schools[$index % $schools->count()]->id;
                $program->save();
            }
            echo "Programs updated with school associations\n";
        }
    }
}
