<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Service\Cup\ClearCupCacheService;
use App\Domain\Cup\CupCacheInvalidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ClearCupCacheServiceTest extends TestCase
{
    private ClearCupCacheService $service;

    private CupCacheInvalidator&MockObject $invalidator;
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ClearCupCacheService(
            $this->invalidator = $this->createMock(CupCacheInvalidator::class),
        );
    }

    #[Test]
    public function it_invalidates_the_shared_cups_cache(): void
    {
        $this->invalidator
            ->expects($this->once())
            ->method('invalidate')
        ;

        $this->service->execute();
    }
}
