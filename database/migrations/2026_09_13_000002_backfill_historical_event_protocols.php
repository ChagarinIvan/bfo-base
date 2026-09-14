<?php

declare(strict_types=1);

use App\Domain\Event\Event;
use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolStatus;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Event::query()
            ->whereNull('active_event_protocol_id')
            ->whereHas('protocolLines')
            ->whereDoesntHave('protocolLines', static fn ($query) => $query->whereNull('person_id'))
            ->eachById(static function (Event $event): void {
                $protocol = EventProtocol::queue($event->id, (string) Str::uuid());
                $protocol->status = EventProtocolStatus::READY;
                $protocol->total_lines = $event->protocolLines()->count();
                $protocol->identified_lines = $protocol->total_lines;
                $protocol->identified_line_ids = $event->protocolLines()->pluck('protocol_lines.id')->map(static fn (int $id): int => $id)->all();
                $protocol->completed_rank_person_ids = [];
                $protocol->created = $event->created;
                $protocol->updated = $event->updated;
                $protocol->save();

                ProtocolLine::query()
                    ->whereHas('distance', static fn ($query) => $query->where('event_id', $event->id))
                    ->update(['event_protocol_id' => $protocol->id]);

                $event->active_event_protocol_id = $protocol->id;
                $event->save();
            });
    }

    public function down(): void
    {
        Event::query()
            ->whereNotNull('active_event_protocol_id')
            ->eachById(static function (Event $event): void {
                $protocol = EventProtocol::query()->find($event->active_event_protocol_id);
                if ($protocol instanceof EventProtocol && $protocol->status === EventProtocolStatus::READY) {
                    $event->active_event_protocol_id = null;
                    $event->save();
                    $protocol->delete();
                }
            });
    }
};
