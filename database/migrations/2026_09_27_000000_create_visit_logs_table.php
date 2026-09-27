<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('visit_logs')) {
            return;
        }

        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nullable on purpose: nearby places come from the Overpass/OSM
            // lookup, and many of those are not in the curated `locations`
            // table. The free-text location_name keeps the visit recorded.
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('location_name');
            $table->dateTime('checked_in_at');
            $table->dateTime('checked_out_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->timestamps();

            // Lookups are always "this user, at this place" and "this user,
            // still inside a place".
            $table->index(['user_id', 'location_name']);
            $table->index(['user_id', 'checked_out_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_logs');
    }
};
