<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'program_id',
        'section_id',
        'email_verification_code',
        'email_verification_code_expires_at',
        'google_id',
        'profile_image',
        'phone',
        'bio',
        'department',
        'title',
        'birth_date',
        'gender',
        'address',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_verification_code_expires_at' => 'datetime',
            'birth_date' => 'date',
            'last_login_at' => 'datetime',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'student_id', 'course_id')
                    ->withPivot('status', 'enrollment_date', 'grade')
                    ->withTimestamps();
    }

    /**
     * Check if the user is enrolled in a specific course.
     * @param int $courseId
     * @return bool
     */
    public function isEnrolledInCourse(int $courseId): bool
    {
        return $this->courses()->where('course_id', $courseId)->exists();
    }
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function submittedAssessments()
    {
        return $this->hasMany(SubmittedAssessment::class, 'student_id');
    }

    public function assessments()
    {
        return $this->hasManyThrough(Assessment::class, Course::class, 'instructor_id', 'course_id', 'id', 'id');
    }
}