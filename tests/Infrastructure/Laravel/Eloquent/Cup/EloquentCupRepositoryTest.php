<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Shared\Criteria;
use App\Infrastructure\Laravel\Eloquent\Cup\EloquentCupRepository;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentCupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentCupRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentCupRepository();
    }

    #[Test]
    public function it_returns_empty_collection_when_no_cups(): void
    {
        $result = $this->repository->byCriteria(Criteria::empty());

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_returns_only_active_cups(): void
    {
        Cup::factory()->createOne(['id' => 1, 'active' => true]);
        Cup::factory()->createOne(['id' => 2, 'active' => false]);

        $result = $this->repository->byCriteria(Criteria::empty());

        $this->assertCount(1, $result);
        $this->assertSame(1, $result->first()->id);
    }

    #[Test]
    public function it_filters_by_visible(): void
    {
        Cup::factory()->createOne(['id' => 1, 'visible' => true]);
        Cup::factory()->createOne(['id' => 2, 'visible' => false]);

        $result = $this->repository->byCriteria(new Criteria(['visible' => true]));

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->visible);
    }

    #[Test]
    public function it_filters_by_year(): void
    {
        Cup::factory()->createOne(['id' => 1, 'year' => Year::y2022]);
        Cup::factory()->createOne(['id' => 2, 'year' => Year::y2023]);

        $result = $this->repository->byCriteria(new Criteria(['year' => Year::y2022]));

        $this->assertCount(1, $result);
        $this->assertSame(Year::y2022, $result->first()->year);
    }

    #[Test]
    public function it_orders_by_id_desc(): void
    {
        Cup::factory()->createOne(['id' => 1]);
        Cup::factory()->createOne(['id' => 2]);
        Cup::factory()->createOne(['id' => 3]);

        $result = $this->repository->byCriteria(Criteria::empty());

        $this->assertSame([3, 2, 1], $result->pluck('id')->all());
    }

    #[Test]
    public function it_finds_active_cup_by_id(): void
    {
        Cup::factory()->createOne(['id' => 1, 'active' => true]);

        $result = $this->repository->byId(1);

        $this->assertInstanceOf(Cup::class, $result);
        $this->assertSame(1, $result->id);
    }

    #[Test]
    public function it_does_not_return_inactive_cup_by_id(): void
    {
        Cup::factory()->createOne(['id' => 1, 'active' => false]);

        $result = $this->repository->byId(1);

        $this->assertNotInstanceOf(Cup::class, $result);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_cup_by_id(): void
    {
        $result = $this->repository->byId(999999);

        $this->assertNotInstanceOf(Cup::class, $result);
    }
}
