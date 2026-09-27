<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * The column was added with a foreign key, so the constraint has to be
     * dropped before the column itself — otherwise `migrate:rollback` fails.
     * Both steps are guarded so this stays safe on databases where the column
     * was created by an older revision of this migration.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('plans', 'user_id')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
