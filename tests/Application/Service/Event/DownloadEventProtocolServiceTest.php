<?php

declare(strict_types=1);

namespace Tests\Application\Service\Event;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\ViewEventProtocolDto;
use App\Application\Service\Event\DownloadEventProtocol;
use App\Application\Service\Event\DownloadEventProtocolService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolStorage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DownloadEventProtocolServiceTest extends TestCase
{
    private DownloadEventProtocolService $service;

    private EventRepository&MockObject $events;

    private MockObject&ProtocolStorage $storage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DownloadEventProtocolService(
            $this->events = $this->createMock(EventRepository::class),
            $this->storage = $this->createMock(ProtocolStorage::class),
            new EventAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_fails_when_event_not_found(): void
    {
        $this->expectException(EventNotFound::class);

        $this->events->expects($this->once())->method('byId')->with(5)->willReturn(null);
        // событие не найдено → к хранилищу не обращаемся
        $this->storage->expects($this->never())->method('get');

        $this->service->execute(new DownloadEventProtocol('5'));
    }

    #[Test]
    public function it_reads_protocol_from_storage_and_assembles_dto(): void
    {
        /** @var Event $event */
        $event = Event::factory()->makeOne(['id' => 5, 'name' => 'Кубок', 'file' => 'protocols/5.xlsx']);
        $protocol = new Protocol('binary-content', 'xlsx');

        $this->events->expects($this->once())->method('byId')->with(5)->willReturn($event);
        $this->storage->expects($this->once())->method('get')->with('protocols/5.xlsx')->willReturn($protocol);

        $result = $this->service->execute(new DownloadEventProtocol('5'));

        $this->assertInstanceOf(ViewEventProtocolDto::class, $result);
        $this->assertSame('binary-content', $result->content);
        $this->assertSame('xlsx', $result->extension);
    }
}
