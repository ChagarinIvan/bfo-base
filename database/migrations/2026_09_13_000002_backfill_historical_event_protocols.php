<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->whereNull('processing_status')
            ->whereNotNull('file')
            ->where('file', '!=', '')
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
    }

    public function down(): void
    {
        // A historical ready value cannot be distinguished from a later completed run.
    }
};
