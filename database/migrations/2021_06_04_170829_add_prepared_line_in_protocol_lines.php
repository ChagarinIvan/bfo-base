<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreparedLineInProtocolLines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->string('prepared_line')->default('')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropColumn('prepared_line');
        });
    }
}
