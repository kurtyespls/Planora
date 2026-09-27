<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the "store money and ratings as loosely typed text, then compensate
 * in every consumer" pattern with real column types.
 *
 *  - hotels.price was varchar(255), so PHP and JS both had to strip currency
 *    formatting before any arithmetic.
 *  - plans.budget was int(11), silently truncating decimal budgets.
 *  - plans.title is added so a saved plan can carry a user-chosen label.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hotels', 'price')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->change();
            });
        }

        if (Schema::hasColumn('plans', 'budget')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->decimal('budget', 12, 2)->change();
            });
        }

        if (!Schema::hasColumn('plans', 'title')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->string('title', 120)->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('plans', 'title')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->dropColumn('title');
            });
        }

        if (Schema::hasColumn('plans', 'budget')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->integer('budget')->change();
            });
        }

        if (Schema::hasColumn('hotels', 'price')) {
            Schema::table('hotels', function (Blueprint $table) {
                $table->string('price', 255)->change();
            });
        }
    }
};
