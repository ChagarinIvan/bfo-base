<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons_payments', static function (Blueprint $table): void {
            $table->id();
        });
    }

    public function down(): void
    {
        Schema::table('persons_payments', static function (Blueprint $table): void {
            $table->dropColumn('id');
        });
    }
};
