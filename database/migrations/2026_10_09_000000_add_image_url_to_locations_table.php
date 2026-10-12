<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-place photo for the Step 03 picker cards.
     *
     * Nullable on purpose: places seeded before this migration (and any
     * future place without an uploaded photo yet) fall back to the
     * per-category image in PlanoraService::PLACE_CATEGORY_IMAGES, so no
     * card ever renders a broken <img>.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
