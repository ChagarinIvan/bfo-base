<?php

namespace Tests\Domain\Event;

use App\Domain\Event\Event;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\ProtocolStorage;
use App\Domain\Event\StandardProtocolUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class StandardProtocolUpdaterTest extends TestCase
{
    private ProtocolStorage&MockObject $protocols;

    private StandardProtocolUpdater $updater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->updater = new StandardProtocolUpdater(
            $this->protocols = $this->createMock(ProtocolStorage::class),
            new ProtocolPathResolver,
        );
    }

    /** @test */
    public function it_updates_protocol(): void
    {
        $protocol = new Protocol('protocol', 'xml');

        $this->protocols
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('initial_file.xml'))
        ;

        $this->protocols
            ->expects($this->once())
            ->method('put')
            ->with($this->equalTo('2023/2023-02-02_test_event@@xml'), $this->identicalTo($protocol))
        ;

        /** @var Event $event */
        $event = Event::factory(state: ['name' => 'test_event', 'date' => '2023-02-02', 'file' => 'initial_file.xml'])->makeOne();
        $path = $this->updater->update($event, $protocol);

        $this->assertEquals('2023/2023-02-02_test_event@@xml', $path);
    }
}
