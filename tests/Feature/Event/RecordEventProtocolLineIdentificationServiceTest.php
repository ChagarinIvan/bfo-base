<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Application\Service\Event\RecordEventProtocolLineIdentification;
use App\Application\Service\Event\RecordEventProtocolLineIdentificationService;
use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolRepository;
use App\Domain\Event\EventProtocolStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RecordEventProtocolLineIdentificationServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_an_identified_line_and_finishes_an_empty_rank_rebuild(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne();
        /** @var Event $event */
        $event = Event::factory()->createOne(['competition_id' => $competition->id]);
        $impression = new Impression(Carbon::parse('2026-09-13 12:00:00'), 1);
        $protocol = EventProtocol::queue($event->id, (string) Str::uuid());
        $protocol->created = $impression;
        $protocol->updated = $impression;
        $protocol->save();
        $protocol->startParsing($impression);
        $protocol->startIdentifying(1, $impression);
        $this->app->get(EventProtocolRepository::class)->update($protocol);

        $this->app->get(RecordEventProtocolLineIdentificationService::class)->execute(
            new RecordEventProtocolLineIdentification($protocol->id, 42, $impression),
        );

        $stored = $this->app->get(EventProtocolRepository::class)->byId($protocol->id);

        $this->assertNotNull($stored);
        $this->assertSame([42], $stored->identified_line_ids);
        $this->assertSame(1, $stored->identified_lines);
        $this->assertSame(EventProtocolStatus::READY, $stored->status);
    }
}
