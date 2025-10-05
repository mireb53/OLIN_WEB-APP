<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('key');
                $table->string('subject');
                $table->longText('body_html');
                $table->longText('body_text')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('school_id')->nullable();
                $table->timestamps();
                $table->index(['key', 'school_id']);
                $table->unique(['key', 'school_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
