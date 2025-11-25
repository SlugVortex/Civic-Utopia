<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ballot_questions', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Constitutional Amendment 4"
            $table->text('official_text'); // The raw legal text
            $table->date('election_date');

            // AI Generated Fields (Nullable initially, filled by Azure)
            $table->text('summary_plain')->nullable(); // Simple English
            $table->text('summary_patois')->nullable(); // Jamaican Patois
            $table->text('yes_vote_meaning')->nullable();
            $table->text('no_vote_meaning')->nullable();
            $table->json('pros')->nullable(); // Array of arguments for
            $table->json('cons')->nullable(); // Array of arguments against

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ballot_questions');
    }
};
