<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'course_code',
        'description',
        'credits',
        'department',
        'program_id',
        'instructor_id',
        'status',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class); 
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
    public function assessments() {
        return $this->hasMany(Assessment::class);
    }
    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'student_id')
                    ->withPivot('status', 'enrollment_date', 'grade') 
                    ->withTimestamps(); 
    }
}