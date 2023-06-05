<?php

use App\Models\Cups\CupType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCupEventType extends Migration
{
    public function up(): void
    {
        Schema::table('cups', function (Blueprint $table) {
            $table->string('type')
                ->nullable(false)
                ->default(CupType::SPRINT)
                ->change()
            ;
        });
    }

    public function down(): void
    {
        Schema::table('cups', function (Blueprint $table) {
            $table->enum('type', [
                CupType::SPRINT,
                CupType::ELITE,
                CupType::MASTER,
                CupType::BIKE,
                CupType::JUNIORS,
                CupType::YOUTH,
            ])
                ->nullable(false)
                ->default(CupType::SPRINT)
                ->change()
            ;
        });
    }
}
