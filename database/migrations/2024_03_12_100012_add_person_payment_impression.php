<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('persons_payments', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by')->nullable(false)->default(1);
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable(false)->default(1);
        });

        DB::table('persons_payments')->update(['created_at' => DB::raw('date')]);
        DB::table('persons_payments')->update(['updated_at' => DB::raw('date')]);

        Schema::table('persons_payments', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->change();
            $table->dateTime('updated_at')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('persons_payments', static function (Blueprint $table): void {
            $table->dropColumn('created_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_at');
            $table->dropColumn('updated_by');
        });
    }
};
