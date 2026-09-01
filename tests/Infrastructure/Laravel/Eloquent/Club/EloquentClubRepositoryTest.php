<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Laravel\Eloquent\Club;

use App\Domain\Club\Club;
use App\Infrastructure\Laravel\Eloquent\Club\EloquentClubRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EloquentClubRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentClubRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentClubRepository;
    }

    #[Test]
    public function it_finds_an_active_club_by_normalized_name(): void
    {
        /** @var Club $club */
        $club = Club::factory()->createOne([
            'id' => 1,
            'normalize_name' => 'тэст клуб',
            'active' => true,
        ]);
        Club::factory()->createOne([
            'id' => 2,
            'normalize_name' => 'тэст клуб',
            'active' => false,
        ]);

        $this->assertSame($club->id, $this->repository->oneByNormalizedName('тэст клуб')?->id);
    }

    #[Test]
    public function it_does_not_find_an_inactive_club_by_normalized_name(): void
    {
        Club::factory()->createOne([
            'normalize_name' => 'няма клуба',
            'active' => false,
        ]);

        $this->assertNotInstanceOf(Club::class, $this->repository->oneByNormalizedName('няма клуба'));
    }
}
