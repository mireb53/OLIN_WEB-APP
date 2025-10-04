<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('failed_logins')) {
            Schema::create('failed_logins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('email')->nullable()->index();
                $table->string('ip_address', 45)->nullable()->index();
                $table->string('user_agent')->nullable();
                $table->string('reason')->nullable();
                $table->timestamps();
                $table->index(['created_at']);
            });
            return;
        }

        // Table exists: add missing columns/indexes if needed
        Schema::table('failed_logins', function (Blueprint $table) {
            if (!Schema::hasColumn('failed_logins', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('failed_logins', 'reason')) {
                $table->string('reason')->nullable()->after('user_agent');
            }
            // ensure created_at index exists (safe to try adding; if exists, DB may error; so guard)
            // Many DBs don't allow conditional index checks via Schema, so skip to avoid errors.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_logins');
    }
};
