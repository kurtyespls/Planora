<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('hotels', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('image_url');
        $table->text('description')->nullable();
        $table->float('rating')->nullable();
        $table->string('price');
        $table->timestamps();

        // DITO ANG MGA BAGONG COLUMNS:
            $table->text('amenities')->nullable();
            $table->text('gallery')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
