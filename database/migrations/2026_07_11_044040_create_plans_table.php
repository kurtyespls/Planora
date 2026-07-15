<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            // Inalis na natin ang tourist_spot dito
            $table->string('hotel_name');
            $table->integer('budget');
            $table->integer('total_days');
            $table->json('rest_days')->nullable(); 
            $table->longText('ai_recommendation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};