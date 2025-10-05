<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	use HasFactory;

	protected $fillable = [
		'school_id',
		'current_semester',
		'academic_year',
		'start_date',
		'end_date',
		'platform_name',
		'default_language',
		'timezone',
		'max_file_size',
		'allowed_file_types',
	];

	public function school()
	{
		return $this->belongsTo(School::class);
	}

	/**
	 * Returns true if today's date is within the configured current semester window (inclusive).
	 * If dates are missing or invalid, returns null to signal unknown state.
	 */
	public function isInCurrentSemester(): ?bool
	{
		if (!$this->start_date || !$this->end_date) {
			return null;
		}
		try {
			$start = \Carbon\Carbon::parse($this->start_date)->startOfDay();
			$end = \Carbon\Carbon::parse($this->end_date)->endOfDay();
			$today = now();
			return $today->between($start, $end, true);
		} catch (\Throwable $e) {
			return null;
		}
	}

	/**
	 * Returns a simple status string for the current term timeline: 'upcoming', 'ongoing', 'ended', or 'unknown'.
	 */
	public function getCurrentSemesterStatus(): string
	{
		if (!$this->start_date || !$this->end_date) {
			return 'unknown';
		}
		try {
			$start = \Carbon\Carbon::parse($this->start_date)->startOfDay();
			$end = \Carbon\Carbon::parse($this->end_date)->endOfDay();
			$today = now();
			if ($today->lt($start)) return 'upcoming';
			if ($today->gt($end)) return 'ended';
			return 'ongoing';
		} catch (\Throwable $e) {
			return 'unknown';
		}
	}
}
