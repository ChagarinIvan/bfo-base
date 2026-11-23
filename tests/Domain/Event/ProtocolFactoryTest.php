<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol\ProtocolFactory;
use App\Domain\Event\Protocol\ProtocolSource;
use App\Domain\Event\Protocol\ProtocolUrlReader;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

final class ProtocolFactoryTest extends TestCase
{
    #[Test]
    public function it_rejects_a_source_without_results_body(): void
    {
        $reader = $this->createStub(ProtocolUrlReader::class);
        $reader->method('read')->willReturn('<html>invalid</html>');
        $this->expectException(InvalidProtocolContent::class);

        new ProtocolFactory($reader)->create(new ProtocolSource(null, 'https://obelarus.net/protocol'));
    }

    #[Test]
    public function it_builds_protocol_from_uploaded_file(): void
    {
        $file = $this->createStub(UploadedFile::class);
        $file->method('getContent')->willReturn('protocol content');
        $file->method('getMimeType')->willReturn('text/plain');

        $reader = $this->createMock(ProtocolUrlReader::class);
        $reader->expects($this->never())->method('read');
        $protocol = new ProtocolFactory($reader)->create(new ProtocolSource($file, null));

        $this->assertSame('protocol content', $protocol->content);
        $this->assertSame('text/plain', $protocol->extension);
    }

    #[Test]
    public function it_extracts_results_body_from_url_source(): void
    {
        $url = 'https://obelarus.net/protocol';
        $reader = $this->createMock(ProtocolUrlReader::class);
        $reader->expects($this->once())->method('read')->with($url)->willReturn('<html><div id="results-body">result</div></html>');

        $protocol = new ProtocolFactory($reader)->create(new ProtocolSource(null, $url));

        $this->assertStringContainsString('result', $protocol->content);
        $this->assertSame('html', $protocol->extension);
    }
}
