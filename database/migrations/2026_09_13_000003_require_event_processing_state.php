<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->whereNull('processing_status')
            ->whereExists(static function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('distances')
                    ->join('protocol_lines', 'protocol_lines.distance_id', '=', 'distances.id')
                    ->whereColumn('distances.event_id', 'events.id');
            })
            ->whereNotExists(static function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('distances')
                    ->join('protocol_lines', 'protocol_lines.distance_id', '=', 'distances.id')
                    ->whereColumn('distances.event_id', 'events.id')
                    ->whereNull('protocol_lines.person_id');
            })
            ->update(['processing_status' => 'ready', 'error_message' => null]);

        DB::table('events')
            ->whereNull('processing_status')
            ->whereExists(static function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('distances')
                    ->join('protocol_lines', 'protocol_lines.distance_id', '=', 'distances.id')
                    ->whereColumn('distances.event_id', 'events.id')
                    ->whereNull('protocol_lines.person_id');
            })
            ->update([
                'processing_status' => 'identifyingError',
                'error_message' => 'Идентификация протокола не завершена. Загрузите его заново.',
            ]);

        DB::table('events')->whereNull('processing_status')->update([
            'processing_status' => 'parsingError',
            'error_message' => 'Протокол не был обработан. Загрузите его заново.',
        ]);

        DB::table('events')->whereNull('processing_token')->select('id')->orderBy('id')->chunkById(500, static function ($events): void {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)->update(['processing_token' => (string) Str::uuid()]);
            }
        });

        Schema::table('events', static function (Blueprint $table): void {
            $table->string('processing_status', 32)->nullable(false)->change();
            $table->uuid('processing_token')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', static function (Blueprint $table): void {
            $table->string('processing_status', 32)->nullable()->change();
            $table->uuid('processing_token')->nullable()->change();
        });
    }
};
