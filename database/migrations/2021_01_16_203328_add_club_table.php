<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClubTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('club', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::table('person', static function (Blueprint $table): void {
            $table->integer('club_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('club');

        Schema::table('person', static function (Blueprint $table): void {
            $table->dropColumn('club_id');
        });
    }
}
