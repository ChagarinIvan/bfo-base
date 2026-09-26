<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Service\Cup\ClearCupCache;
use App\Application\Service\Cup\ClearCupCacheService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Cup\CupRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ClearCupCacheServiceTest extends TestCase
{
    private ClearCupCacheService $service;

    private CupCacheInvalidator&MockObject $invalidator;
    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ClearCupCacheService(
            $this->invalidator = $this->createMock(CupCacheInvalidator::class),
            $this->cups = $this->createMock(CupRepository::class),
        );
    }

    #[Test]
    public function it_invalidates_cup_cache(): void
    {
        $this->cups->expects($this->once())->method('byId')->with(42)->willReturn($this->createStub(Cup::class));
        $this->invalidator
            ->expects($this->once())
            ->method('invalidate')
            ->with(42)
        ;

        $this->service->execute(new ClearCupCache('42'));
    }

    #[Test]
    public function it_rejects_a_missing_cup(): void
    {
        $this->cups->expects($this->once())->method('byId')->with(42)->willReturn(null);
        $this->invalidator->expects($this->never())->method('invalidate');
        $this->expectException(CupNotFound::class);

        $this->service->execute(new ClearCupCache('42'));
    }
}
