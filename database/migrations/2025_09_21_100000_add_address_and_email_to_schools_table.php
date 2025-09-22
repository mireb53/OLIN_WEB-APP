<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('schools', function (Blueprint $table) {
			if (!Schema::hasColumn('schools','address')) {
				$table->string('address')->nullable()->after('code');
			}
			if (!Schema::hasColumn('schools','email')) {
				$table->string('email')->nullable()->after('address');
			}
		});
	}

	public function down(): void
	{
		Schema::table('schools', function (Blueprint $table) {
			if (Schema::hasColumn('schools','email')) {
				$table->dropColumn('email');
			}
			if (Schema::hasColumn('schools','address')) {
				$table->dropColumn('address');
			}
		});
	}
};
