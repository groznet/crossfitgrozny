<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('public_token', 40)->nullable()->unique()->after('status');
        });

        // Backfill any members that existed before this column did — new
        // members get one automatically via Member::booted().
        foreach (DB::table('members')->whereNull('public_token')->pluck('id') as $id) {
            DB::table('members')->where('id', $id)->update([
                'public_token' => Str::random(32),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
