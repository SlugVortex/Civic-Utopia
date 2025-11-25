<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Uploader
            $table->string('title');
            $table->string('type')->default('Unknown'); // Bill, Policy, Report, etc.
            $table->string('country')->default('Jamaica');
            $table->string('file_path'); // Path to PDF
            $table->longText('extracted_text')->nullable(); // The text pulled from the PDF

            // AI Analysis
            $table->text('summary_plain')->nullable();
            $table->text('summary_eli5')->nullable(); // Explain Like I'm 5
            $table->boolean('is_public')->default(false); // Draft vs Published

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
