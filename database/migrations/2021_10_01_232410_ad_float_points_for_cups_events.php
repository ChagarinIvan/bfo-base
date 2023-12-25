<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AdFloatPointsForCupsEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('cup_events', static function (Blueprint $table): void {
            $table->float('points')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('cup_events', static function (Blueprint $table): void {
            $table->integer('points')->nullable(false)->change();
        });
    }
}
