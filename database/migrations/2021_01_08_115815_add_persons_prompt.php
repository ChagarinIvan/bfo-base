<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonsPrompt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->json('prompt');
            $table->dropColumn('patronymic');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->dropColumn('prompt');
            $table->string('patronymic')->nullable(true);
        });
    }
}
