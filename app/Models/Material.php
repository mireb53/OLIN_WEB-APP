<?php

// app/Models/Material.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'topic_id',
        'title',
        'description',
        'file_path',
        'original_filename',
        'material_type',
        'available_at',
        'unavailable_at',
    ];


    protected $casts = [
        'available_at' => 'datetime',
        'unavailable_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d\TH:i:sP'); // ISO 8601 with timezone (e.g., 2025-07-20T12:00:00+08:00)
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function topic() {
        return $this->belongsTo(Topic::class);
    }
    /**
     * Helper to check if the material is currently available.
     */
    public function isAvailable(): bool
    {
        $now = now();
        return (is_null($this->available_at) || $this->available_at->lte($now)) &&
               (is_null($this->unavailable_at) || $this->unavailable_at->gte($now));
    }
}