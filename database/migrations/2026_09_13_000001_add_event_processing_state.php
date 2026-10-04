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
        Schema::table('events', static function (Blueprint $table): void {
            $table->string('processing_status', 32)->nullable()->after('file')->index();
            $table->uuid('processing_token')->nullable()->after('processing_status');
            $table->text('error_message')->nullable()->after('processing_token');
        });

        DB::table('events')
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
            ->update(['processing_status' => 'ready']);

        DB::table('events')
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

        DB::table('events')->select('id')->orderBy('id')->chunkById(500, static function ($events): void {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)->update(['processing_token' => (string) Str::uuid()]);
            }
        });

        Schema::table('events', static function (Blueprint $table): void {
            $table->string('processing_status', 32)->nullable(false)->change();
            $table->uuid('processing_token')->nullable(false)->change();
        });

        Schema::drop('protocol_ident_queue');
    }

    public function down(): void
    {
        Schema::create('protocol_ident_queue', static function (Blueprint $table): void {
            $table->id();
            $table->string('ident_line')->unique()->default('')->index();
        });

        Schema::table('events', static function (Blueprint $table): void {
            $table->dropIndex(['processing_status']);
            $table->dropColumn(['processing_status', 'processing_token', 'error_message']);
        });
    }
};
