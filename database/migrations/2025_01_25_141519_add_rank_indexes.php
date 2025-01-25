<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->index('person_id');
            $table->index('finish_date');
            $table->index('start_date');
            $table->index('activated_date');
        });

        Schema::table('person', static function (Blueprint $table): void {
            $table->index('active');
        });

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->index('person_id');
            $table->index('prepared_line');
            $table->index('activate_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->dropIndex('person_id');
            $table->dropIndex('finish_date');
            $table->dropIndex('start_date');
            $table->dropIndex('activated_date');
        });

        Schema::table('person', static function (Blueprint $table): void {
            $table->dropIndex('active');
        });

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropIndex('person_id');
            $table->dropIndex('prepared_line');
            $table->dropIndex('activate_rank');
        });
    }
};
