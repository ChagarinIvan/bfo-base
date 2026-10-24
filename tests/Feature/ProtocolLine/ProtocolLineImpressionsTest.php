<?php

declare(strict_types=1);

namespace Tests\Feature\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLineImpressionsTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{string}> */
    public static function mutations(): iterable
    {
        yield 'activation' => ['activateRank'];
        yield 'assignment' => ['assignPerson'];
        yield 'identification' => ['setPerson'];
    }

    #[Test]
    #[DataProvider('mutations')]
    public function a_mutation_preserves_creation_and_records_its_own_impression(string $method): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $person = Person::factory()->createOne(['id' => null]);
        $created = new Impression(Carbon::parse('2026-10-24 10:00:00'), 42);
        $updated = new Impression(Carbon::parse('2026-10-24 11:00:00'), 43);
        $this->app->instance(Clock::class, new FrozenClock($updated->at));
        $line = ProtocolLine::factory()->createOne(['distance_id' => 104, 'created' => $created, 'updated' => $created]);
        if ($method === 'activateRank') {
            $line->activateRank($line->distance->event->date->copy(), $updated);
        } elseif ($method === 'assignPerson') {
            $line->assignPerson($person->id, $updated);
        } else {
            $line->setPerson($person->id, $updated);
        }
        $this->app->make(ProtocolLineRepository::class)->update($line);
        $saved = $line->fresh();
        $this->assertEquals($created, $saved->created);
        $this->assertEquals($updated, $saved->updated);
    }
}
