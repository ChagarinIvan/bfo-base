<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewCupServiceTest extends TestCase
{
    private ViewCupService $service;

    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();
        $authAssembler = new AuthAssembler;

        $this->service = new ViewCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new CupAssembler($authAssembler, new EventAssembler($authAssembler)),
        );
    }

    #[Test]
    public function it_fails_when_cup_not_found(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn(null)
        ;

        $command = new ViewCup('1');
        $this->service->execute($command);
    }

    #[Test]
    public function it_shows_cup(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();
        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn($cup)
        ;

        $command = new ViewCup('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewCupDto::class, $result);
        $this->assertEquals($cup->id, $result->id);
    }
}
