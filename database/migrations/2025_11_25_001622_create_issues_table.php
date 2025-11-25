<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable(); // Can be auto-generated later
            $table->string('location'); // e.g., "12 Half Way Tree Road"
            $table->string('image_path');
            $table->text('user_description')->nullable();

            // AI Fields
            $table->json('ai_tags')->nullable(); // What Azure Vision saw (pothole, asphalt, danger)
            $table->text('ai_caption')->nullable(); // Azure Vision's sentence description
            $table->string('severity')->nullable(); // Critical, Moderate, Low
            $table->text('generated_letter')->nullable(); // The formal letter
            $table->string('status')->default('Reported'); // Reported, Drafted, Sent

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
