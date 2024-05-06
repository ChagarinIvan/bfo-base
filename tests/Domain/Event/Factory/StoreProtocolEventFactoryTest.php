<?php

declare(strict_types=1);

namespace Tests\Domain\Event\Factory;

use App\Domain\Event\Event;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\EventInput;
use App\Domain\Event\Factory\StoreProtocolEventFactory;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\ProtocolStorage;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class StoreProtocolEventFactoryTest extends TestCase
{
    private EventFactory&MockObject $decorated;

    private ProtocolStorage&MockObject $protocols;

    private StoreProtocolEventFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new StoreProtocolEventFactory(
            $this->decorated = $this->createMock(EventFactory::class),
            $this->protocols = $this->createMock(ProtocolStorage::class),
            new ProtocolPathResolver(),
        );
    }

    /** @test */
    public function it_stores_protocol(): void
    {
        $protocol = new Protocol('protocol', 'xml');
        $input = new EventInput(
            new EventInfo('name', 'description', Carbon::parse('2023-04-01')),
            1,
            1,
            $protocol
        );

        $this->protocols
            ->expects($this->once())
            ->method('put')
            ->with($this->equalTo('2023/2023-04-01_name@@xml'), $this->identicalTo($protocol))
        ;

        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->decorated
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($input->withFile('2023/2023-04-01_name@@xml')))
            ->willReturn($event)
        ;

        $this->factory->create($input);
    }
}
