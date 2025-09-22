<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School;
use Illuminate\Support\Str;

class FixMissingSchoolCodes extends Command
{
    protected $signature = 'schools:fix-codes';
    protected $description = 'Fix missing codes in school records';

    public function handle()
    {
        $schoolsWithoutCode = School::whereNull('code')->orWhere('code', '')->get();
        
        if ($schoolsWithoutCode->isEmpty()) {
            $this->info('No schools found with missing codes.');
            return 0;
        }
        
        $this->info("Found {$schoolsWithoutCode->count()} schools with missing codes.");
        
        foreach ($schoolsWithoutCode as $school) {
            $baseCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($school->name, 0, 8)));
            if (empty($baseCode)) {
                $baseCode = 'SCH';
            }
            
            $code = $baseCode;
            $counter = 1;
            
            while (School::where('code', $code)->where('id', '!=', $school->id)->exists()) {
                $code = $baseCode . $counter;
                $counter++;
            }
            
            $school->code = $code;
            $school->save();
            
            $this->line("Updated school '{$school->name}' (ID: {$school->id}) with code: {$code}");
        }
        
        $this->info('All missing school codes have been fixed.');
        return 0;
    }
}