<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
        });
        Schema::table('comments', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) { $table->dropColumn(['is_flagged', 'flag_reason']); });
        Schema::table('comments', function (Blueprint $table) { $table->dropColumn(['is_flagged', 'flag_reason']); });
    }
};
