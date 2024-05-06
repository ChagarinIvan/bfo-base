<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cup_events', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->default(10);
            $table->dateTime('updated_at')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->default(10);
        });

        Schema::table('cup_events', static function (Blueprint $table): void {
            $table->integer('created_by')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
            $table->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('cup_events', static function (Blueprint $table): void {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('active');
        });
    }
};
