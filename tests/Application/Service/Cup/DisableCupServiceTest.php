<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Cup\DisableCup;
use App\Application\Service\Cup\DisableCupService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableCupServiceTest extends TestCase
{
    private DisableCupService $service;

    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisableCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new FrozenClock(),
            new DummyTransactional(),
        );
    }

    /** @test */
    public function it_fails_when_cup_not_found(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new DisableCup('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_cup(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($cup)
        ;

        $this->cups
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($cup))
        ;

        $command = new DisableCup('1', new UserId(1));

        $this->service->execute($command);

        $this->assertFalse($cup->active);
    }
}
