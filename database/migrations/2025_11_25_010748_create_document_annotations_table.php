<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('note'); // The comment/annotation
            $table->string('section_reference')->nullable(); // e.g. "Page 5" or "Section 2.1"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_annotations');
    }
};
