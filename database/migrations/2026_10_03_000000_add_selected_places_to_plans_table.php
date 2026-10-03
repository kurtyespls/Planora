<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers the places the traveller explicitly ticked in the Step 03 picker.
 *
 * Without this a saved plan records only what the AI happened to suggest, so a
 * later "Regenerate" would silently drop the choices that made the plan theirs.
 *
 * Stores the canonical "Name (Category)" labels produced by
 * PlanoraService::selectedPlaceLabels(), not the raw strings from the browser.
 *
 * Nullable on purpose: plans saved before this migration have no picks, and
 * every read path already treats null as "the traveller chose nothing".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('plans', 'selected_places')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->json('selected_places')->nullable()->after('rest_days');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('plans', 'selected_places')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('selected_places');
        });
    }
};