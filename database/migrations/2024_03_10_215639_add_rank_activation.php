<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->date('activated_date')->nullable();
            $table->dropColumn('active');
        });
    }

    public function down(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->dropColumn('activated_date');
            $table->boolean('active')->nullable(false)->default(true);
        });
    }
};
