<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', static function (Blueprint $table): void {
            $table->string('processing_status', 32)->nullable()->after('file')->index();
            $table->uuid('processing_token')->nullable()->after('processing_status');
            $table->text('error_message')->nullable()->after('processing_token');
        });
    }

    public function down(): void
    {
        Schema::table('events', static function (Blueprint $table): void {
            $table->dropColumn(['processing_status', 'processing_token', 'error_message']);
        });
    }
};
