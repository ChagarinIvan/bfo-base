<?php

declare(strict_types=1);

namespace Tests\Repositories;

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
        $this->assertInstanceOf(\App\Domain\ProtocolLine\ProtocolLine::class, $protocolLine);
    }

    #[DataProvider('criteriaDataProvider')]
    #[Test]
    public function it_gets_lines_by_criteria(int $expectedCount, Criteria $criteria): void
    {
        $this->seed(ProtocolLinesSeeder::class);
        $protocolLines = $this->repository->byCriteria($criteria);
        $this->assertCount($expectedCount, $protocolLines);
    }
}
