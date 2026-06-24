<?php

declare(strict_types=1);

namespace Tests\Repositories;

use App\Domain\Competition\Competition;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use App\Repositories\ProtocolLinesRepository;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProtocolLinesRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProtocolLinesRepository $repository;

    public static function criteriaDataProvider(): Iterator
    {
        yield 'by distance' => [2, new Criteria(['distances' => collect([101])])];
        yield 'by distances' => [4, new Criteria(['distances' => collect([101, 102, 103])])];
        yield 'with payment year' => [2, new Criteria(['distances' => collect([101, 102, 103]), 'paymentYear' => 2024, 'eventDate' => '2024-01-12'])];
        yield 'with previous payment year' => [3, new Criteria(['distances' => collect([101, 102, 103]), 'paymentYear' => 2023, 'eventDate' => '2024-01-12'])];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $app = $this->createApplication();
        RefreshDatabaseState::$migrated = false;
        $this->repository = new ProtocolLinesRepository($app->get(ConnectionInterface::class));
    }

    #[Test]
    public function it_gets_line_by_id(): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $protocolLine = $this->repository->byId(101);
        $this->assertInstanceOf(ProtocolLine::class, $protocolLine);
    }

    #[DataProvider('criteriaDataProvider')]
    #[Test]
    public function it_gets_lines_by_criteria(int $expectedCount, Criteria $criteria): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $protocolLines = $this->repository->byCriteria($criteria);
        $this->assertCount($expectedCount, $protocolLines);
    }

    #[Test]
    public function it_idents_by_person_prompt_only_for_active_persons(): void
    {
        Person::factory(state: ['id' => 1, 'active' => false])->createOne();
        Person::factory(state: ['id' => 2, 'active' => true])->createOne();
        PersonPrompt::factory(state: ['person_id' => 1, 'prompt' => 'same prompt'])->createOne();

        $this->createProtocolLine(id: 101, preparedLine: 'same prompt');

        $this->repository->identByEqualPersonPrompt(collect([101]));

        $this->assertNull(ProtocolLine::find(101)->person_id);

        PersonPrompt::factory(state: ['person_id' => 2, 'prompt' => 'same prompt'])->createOne();

        $this->repository->identByEqualPersonPrompt(collect([101]));

        $this->assertSame(2, ProtocolLine::find(101)->person_id);
    }

    private function createProtocolLine(int $id, string $preparedLine): void
    {
        Competition::factory(state: ['id' => 101])->createOne();
        Event::factory(state: ['id' => 101, 'competition_id' => 101])->createOne();
        Group::factory(state: ['id' => 101])->createOne();
        Distance::factory(state: ['id' => 101, 'event_id' => 101, 'group_id' => 101])->createOne();
        ProtocolLine::factory(state: [
            'id' => $id,
            'distance_id' => 101,
            'person_id' => null,
            'prepared_line' => $preparedLine,
        ])->createOne();
    }
}
