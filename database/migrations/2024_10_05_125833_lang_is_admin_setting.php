<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->where('key', '=', 'lang')->update(['cat' => 'Admin']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->where('key', '=', 'lang')->update(['cat' => 'Gallery']);
	}
};
