<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetphoneField extends Migration
{
    public function up(): void
    {
        Schema::table('persons_prompt', static function (Blueprint $table): void {
            $table
                ->string('metaphone')
                ->nullable(false)
                ->default('')
                ->index()
            ;
        });
    }

    public function down(): void
    {
        Schema::table('persons_prompt', static function (Blueprint $table): void {
            $table->dropColumn('metaphone');
        });
    }
}
