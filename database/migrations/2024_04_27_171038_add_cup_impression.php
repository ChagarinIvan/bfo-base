<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->default(10);
            $table->dateTime('updated_at')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->default(10);
            $table->json('result')->nullable()->default(null);
        });

        Schema::table('cups', static function (Blueprint $table): void {
            $table->integer('created_by')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->dropColumn('updated_at');
            $table->dropColumn('updated_by');
        });
    }
};
