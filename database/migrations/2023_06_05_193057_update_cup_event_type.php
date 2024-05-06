<?php

declare(strict_types=1);

use App\Domain\Cup\CupType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCupEventType extends Migration
{
    public function up(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->string('type')
                ->nullable(false)
                ->default(CupType::SPRINT->value)
                ->change()
            ;
        });
    }

    public function down(): void
    {
        Schema::table('cups', static function (Blueprint $table): void {
            $table->enum('type', array_map(static fn (CupType $type): string => $type->value, CupType::cases()))
                ->nullable(false)
                ->default(CupType::SPRINT)
                ->change()
            ;
        });
    }
}
