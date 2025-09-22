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
}
