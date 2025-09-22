<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            // Academic
            $table->string('current_semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            // System (global when school_id is null)
            $table->string('platform_name')->nullable();
            $table->string('default_language')->nullable();
            $table->string('timezone')->nullable();
            // Upload constraints
            $table->string('max_file_size')->nullable(); // store like "100 MB"
            $table->string('allowed_file_types')->nullable(); // comma separated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
