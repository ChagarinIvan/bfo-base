<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Distance;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListDistancesActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_the_disqualification_flag_as_a_boolean(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->createOne(['id' => 1]);
        /** @var Event $event */
        $event = Event::factory()->createOne(['id' => 1, 'competition_id' => $competition->id]);
        /** @var Group $group */
        $group = Group::factory()->createOne(['id' => 1]);
        Distance::factory()->createOne([
            'id' => 1,
            'event_id' => $event->id,
            'group_id' => $group->id,
            'disqual' => 1,
        ]);

        $this->getJson("/api/v1/distances?eventId={$event->id}")
            ->assertOk()
            ->assertJsonPath('0.disqual', true)
        ;
    }
}
