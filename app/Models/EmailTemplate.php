<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',         // unique key e.g. verify_email, admin_verification, general_notification
        'subject',
        'body_html',   // HTML content with Blade variables like {{ user_name }}
        'body_text',   // optional plain text fallback
        'is_active',
        'school_id',   // nullable; reserved for per-school overrides
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
