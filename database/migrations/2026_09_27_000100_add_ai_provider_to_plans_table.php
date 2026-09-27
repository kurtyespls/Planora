<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records which engine produced the itinerary so the UI can stop implying
     * that every plan came from the AI when the API key is not configured.
     */
    public function up(): void
    {
        if (Schema::hasColumn('plans', 'ai_provider')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->string('ai_provider', 20)->default('local')->after('ai_recommendation');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('plans', 'ai_provider')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('ai_provider');
        });
    }
};
