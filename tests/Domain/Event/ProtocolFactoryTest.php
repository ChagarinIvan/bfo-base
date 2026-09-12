<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Event\Exception\InvalidProtocolContent;
use App\Domain\Event\Protocol\ProtocolFactory;
use App\Domain\Event\Protocol\ProtocolSource;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

final class ProtocolFactoryTest extends TestCase
{
    #[Test]
    public function it_rejects_a_source_without_results_body(): void
    {
        $this->expectException(InvalidProtocolContent::class);

        (new ProtocolFactory)->create(new ProtocolSource(null, 'data://text/plain,invalid'));
    }

    #[Test]
    public function it_builds_protocol_from_uploaded_file(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getContent')->willReturn('protocol content');
        $file->method('getMimeType')->willReturn('text/plain');

        $protocol = (new ProtocolFactory)->create(new ProtocolSource($file, null));

        $this->assertSame('protocol content', $protocol->content);
        $this->assertSame('text/plain', $protocol->extension);
    }

    #[Test]
    public function it_extracts_results_body_from_url_source(): void
    {
        $protocol = (new ProtocolFactory)->create(new ProtocolSource(
            null,
            'data://text/plain,%3Chtml%3E%3Cdiv%20id%3D%22results-body%22%3Eresult%3C%2Fdiv%3E%3C%2Fhtml%3E',
        ));

        $this->assertStringContainsString('result', $protocol->content);
        $this->assertSame('html', $protocol->extension);
    }
}
