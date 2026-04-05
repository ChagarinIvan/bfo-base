<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\CupSearchDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\ListCup;
use App\Application\Service\Cup\ListCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListCupServiceTest extends TestCase
{
    private ListCupService $service;

    private CupRepository&MockObject $cups;

    protected function setUp(): void
    {
        parent::setUp();
        $authAssembler = new AuthAssembler;

        $this->service = new ListCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new CupAssembler(
                $this->createStub(EventRepository::class),
                new EventAssembler($authAssembler),
                $authAssembler,
            )
        );
    }

    #[Test]
    public function it_gets_list_of_cups(): void
    {
        /** @var Cup[] $cups */
        $cups = Cup::factory(count: 2)->make();

        $this->cups
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['year' => '2021', 'visible' => true]))
            ->willReturn($cups)
        ;

        $dto = new CupSearchDto('2021');

        $command = new ListCup($dto);
        $result = $this->service->execute($command);

        $this->assertIsList($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ViewCupDto::class, $result);
        $this->assertEquals($cups[1]->id, $result[1]->id);
    }
}
