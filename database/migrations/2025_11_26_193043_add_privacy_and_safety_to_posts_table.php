<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Privacy for Local News Feed
            if (!Schema::hasColumn('posts', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('content');
            }

            // Content Safety Flags (If not already added)
            if (!Schema::hasColumn('posts', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('is_private');
                $table->string('flag_reason')->nullable()->after('is_flagged');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_private', 'is_flagged', 'flag_reason']);
        });
    }
};
