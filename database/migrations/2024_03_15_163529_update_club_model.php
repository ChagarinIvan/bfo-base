<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club', static function (Blueprint $table): void {
            $table->boolean('active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by')->nullable(false)->default(10);
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable(false)->default(10);
        });

        DB::table('club')->update(['created_at' => Carbon::now()]);
        DB::table('club')->update(['updated_at' => Carbon::now()]);

        Schema::table('club', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->change();
            $table->dateTime('updated_at')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('club', static function (Blueprint $table): void {
            $table->dropColumn('active');
            $table->dropColumn('created_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_at');
            $table->dropColumn('updated_by');
        });
    }
};
