<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'author_id',
        'school_id',
        'status',
        'is_pinned',
        'expires_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected $dates = [
        'expires_at',
        'created_at',
        'updated_at',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope: Active announcements only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    /**
     * Scope: Filter by school
     */
    public function scopeForSchool($query, $schoolId = null)
    {
        if ($schoolId) {
            return $query->where(function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereNull('school_id'); // Include system-wide announcements
            });
        }
        return $query->whereNull('school_id'); // Only system-wide for super admin
    }

    /**
     * Check if announcement is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}