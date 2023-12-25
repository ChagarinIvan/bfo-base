<?php

declare(strict_types=1);

use App\Models\Cups\CupType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCupEventField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->enum('type', [
                CupType::SPRINT,
                CupType::ELITE,
                CupType::MASTER,
                CupType::BIKE,
                CupType::JUNIORS,
                CupType::YOUTH,
            ])->nullable(false)->default(CupType::SPRINT);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
}
