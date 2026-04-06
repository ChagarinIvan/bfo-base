<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\DisableCup;
use App\Application\Service\Cup\DisableCupService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableCupServiceTest extends TestCase
{
    private DisableCupService $service;

    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();

        $authAssembler = new AuthAssembler();
        $this->service = new DisableCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new FrozenClock(),
            new CupAssembler(
                $this->createStub(EventRepository::class),
                new EventAssembler($authAssembler),
                $authAssembler,
            ),
            new DummyTransactional(),
        );
    }

    #[Test]
    public function it_fails_when_cup_not_found(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with(1)
            ->willReturn(null)
        ;

        $command = new DisableCup('1', new UserId(1));
        $this->service->execute($command);
    }

    #[Test]
    public function it_disables_cup(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with(1)
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
