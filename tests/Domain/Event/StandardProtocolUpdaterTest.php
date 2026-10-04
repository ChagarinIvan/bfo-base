<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\StandardProtocolUpdater;
use App\Domain\Shared\Storage;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

final class StandardProtocolUpdaterTest extends TestCase
{
    private MockObject&Storage $protocols;

    private StandardProtocolUpdater $updater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->updater = new StandardProtocolUpdater(
            $this->protocols = $this->createMock(Storage::class),
            new ProtocolPathResolver,
        );
    }

    #[Test]
    public function it_updates_protocol(): void
    {
        $protocol = new Protocol('protocol', 'xml');

        $this->protocols
            ->expects($this->once())
            ->method('delete')
            ->with('initial_file.xml')
        ;

        $this->protocols
            ->expects($this->once())
            ->method('put')
            ->with('2023/2023-02-02_test_event@@xml', $protocol->content)
        ;

        /** @var Event $event */
        $event = Event::factory(state: ['name' => 'test_event', 'date' => '2023-02-02', 'file' => 'initial_file.xml'])->makeOne();
        $path = $this->updater->update($event, $protocol, new Impression(Carbon::parse('2026-09-13'), 1));

        $this->assertSame('2023/2023-02-02_test_event@@xml', $path);
    }

    #[Test]
    public function it_keeps_the_existing_file_when_the_replacement_cannot_be_stored(): void
    {
        $this->protocols->expects($this->once())->method('put')->willThrowException(new RuntimeException('Storage unavailable'));
        $this->protocols->expects($this->never())->method('delete');
        $event = Event::factory(state: ['name' => 'test_event', 'date' => '2023-02-02', 'file' => 'initial_file.xml'])->makeOne();

        $this->expectException(RuntimeException::class);
        $this->updater->update($event, new Protocol('protocol', 'xml'), new Impression(Carbon::parse('2026-09-13'), 1));
    }

    #[Test]
    public function it_keeps_the_new_content_when_the_path_is_unchanged(): void
    {
        $path = '2023/2023-02-02_test_event@@xml';
        $event = Event::factory(state: ['name' => 'test_event', 'date' => '2023-02-02', 'file' => $path])->makeOne();
        $this->protocols->expects($this->once())->method('put')->with($path, 'new content');
        $this->protocols->expects($this->never())->method('delete');

        $this->assertSame($path, $this->updater->update(
            $event,
            new Protocol('new content', 'xml'),
            new Impression(Carbon::parse('2026-09-13'), 1),
        ));
    }
}
