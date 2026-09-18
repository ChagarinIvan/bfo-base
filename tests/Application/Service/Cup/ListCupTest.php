<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Cup\SearchCupDto;
use App\Application\Service\Cup\ListCup;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ListCupTest extends TestCase
{
    #[Test]
    public function it_filters_visible_cups_by_default(): void
    {
        $criteria = new ListCup(new SearchCupDto, false)->criteria();

        $this->assertSame(['visible' => true], $criteria->params());
    }

    #[Test]
    public function authenticated_user_can_select_all_or_invisible_cups(): void
    {
        $allCriteria = new ListCup(new SearchCupDto, true)
            ->criteria();
        $visibleCriteria = new ListCup(new SearchCupDto(visible: true), true)
            ->criteria();
        $invisibleCriteria = new ListCup(new SearchCupDto(visible: false), true)
            ->criteria();

        $this->assertSame([], $allCriteria->params());
        $this->assertSame(['visible' => true], $visibleCriteria->params());
        $this->assertSame(['visible' => false], $invisibleCriteria->params());
    }
}
