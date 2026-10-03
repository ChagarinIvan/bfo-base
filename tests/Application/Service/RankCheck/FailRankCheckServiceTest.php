<?php

declare(strict_types=1);

namespace Tests\Application\Service\RankCheck;

use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Application\Service\RankCheck\FailRankCheck;
use App\Application\Service\RankCheck\FailRankCheckService;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\DummyTransactional;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FailRankCheckServiceTest extends TestCase
{
    #[Test]
    public function a_missing_check_is_rejected_without_writing(): void
    {
        $checks = $this->createMock(RankCheckRepository::class);
        $checks->expects($this->once())->method('lockById')->with(20)->willReturn(null);
        $checks->expects($this->never())->method('update');
        $clock = $this->createMock(Clock::class);
        $clock->expects($this->never())->method('now');
        $service = new FailRankCheckService($checks, new DummyTransactional(), $clock);

        $this->expectException(RankCheckNotFound::class);

        $service->execute(new FailRankCheck(20));
    }
}
