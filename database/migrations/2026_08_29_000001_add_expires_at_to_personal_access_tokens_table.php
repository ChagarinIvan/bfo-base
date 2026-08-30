<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            !Schema::hasTable('personal_access_tokens')
            || Schema::hasColumn('personal_access_tokens', 'expires_at')
        ) {
            return;
        }

        Schema::table('personal_access_tokens', static function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('last_used_at');
        });
    }

    public function down(): void
    {
        // The column may have been created by the original table migration.
        // Never remove it while rolling back this compatibility migration.
    }
};
