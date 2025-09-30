<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type')->nullable();
                $table->string('title')->nullable();
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->index(['user_id', 'is_read']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally conservative: do not drop the table in down to avoid accidental data loss
        // If needed, uncomment the next line:
        // Schema::dropIfExists('notifications');
    }
};
