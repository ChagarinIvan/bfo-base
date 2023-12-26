<?php

declare(strict_types=1);

use App\Models\ProtocolLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->boolean('active')->nullable(false)->default(true);
        });

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->boolean('activate_rank')->nullable()->default(null);
        });

        foreach (ProtocolLine::all() as $line) {
            $line->activate_rank = $line->event->date;
            $line->save();
        }
    }

    public function down(): void
    {
        Schema::table('ranks', static function (Blueprint $table): void {
            $table->dropColumn('active');
        });
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropColumn('activate_rank');
        });
    }
};
