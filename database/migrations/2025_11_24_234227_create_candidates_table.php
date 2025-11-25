<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('party'); // e.g., "JLP", "PNP"
            $table->string('office'); // e.g., "Prime Minister", "MP St. Andrew"
            $table->string('photo_url')->nullable();

            // The Raw Input (The Source of Truth)
            $table->text('manifesto_text');

            // The AI Output (Structured Stances)
            $table->json('stances')->nullable(); // Stores { "crime": "...", "economy": "..." }
            $table->text('ai_summary')->nullable(); // A neutral paragraph summary

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
