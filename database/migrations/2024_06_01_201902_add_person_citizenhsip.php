<?php

declare(strict_types=1);

use App\Domain\Person\Citizenship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->string('citizenship', 30)->default(Citizenship::BELARUS)->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->dropColumn('active');
        });
    }
};
