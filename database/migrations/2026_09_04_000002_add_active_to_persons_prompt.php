<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons_prompt', static function (Blueprint $table): void {
            $table->boolean('active')->default(true)->after('metaphone')->index();
        });
    }

    public function down(): void
    {
        Schema::table('persons_prompt', static function (Blueprint $table): void {
            $table->dropColumn('active');
        });
    }
};
