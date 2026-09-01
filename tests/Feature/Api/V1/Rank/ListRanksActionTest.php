<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Rank;

use App\Domain\Rank\Rank;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_map;

final class ListRanksActionTest extends TestCase
{
    #[Test]
    public function it_returns_the_rank_catalog_in_enum_order(): void
    {
        $this->getJson('/api/v1/ranks')
            ->assertOk()
            ->assertExactJson(array_map(
                static fn (Rank $rank): array => ['id' => $rank->value, 'label' => $rank->label()],
                Rank::cases(),
            ));
    }

    #[Test]
    public function it_is_public_and_get_only(): void
    {
        $this->getJson('/api/v1/ranks')->assertOk();
        $this->postJson('/api/v1/ranks')->assertMethodNotAllowed();
    }
}
