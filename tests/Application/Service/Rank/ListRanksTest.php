<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Service\Rank\ListRanks;
use App\Domain\Rank\Rank;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_last;

final class ListRanksTest extends TestCase
{
    #[Test]
    public function it_returns_the_enum_catalog_for_api_consumers(): void
    {
        $result = new ListRanks()->execute();

        $this->assertSame(Rank::cases()[0]->value, $result[0]['id']);
        $this->assertSame(Rank::cases()[0]->label(), $result[0]['label']);
        $this->assertSame('without_rank', array_last($result)['id']);
    }
}
