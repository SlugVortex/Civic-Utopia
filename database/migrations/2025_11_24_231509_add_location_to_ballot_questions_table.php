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
        Schema::table('ballot_questions', function (Blueprint $table) {
            $table->string('country')->default('Jamaica')->after('election_date');
            $table->string('region')->nullable()->after('country'); // e.g. "Kingston", "California"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ballot_questions', function (Blueprint $table) {
            $table->dropColumn(['country', 'region']);
        });
    }
};
