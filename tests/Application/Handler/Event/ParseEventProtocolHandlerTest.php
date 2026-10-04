<?php

declare(strict_types=1);

namespace Tests\Application\Handler\Event;

use App\Application\Handler\Event\ParseEventProtocolHandler;
use App\Domain\Event\ProtocolParser;
use App\Domain\Event\StandardProtocolParser;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class ParseEventProtocolHandlerTest extends TestCase
{
    #[Test]
    public function it_is_queued_after_the_event_creation_transaction_commits(): void
    {
        $this->assertTrue(new ReflectionClass(ParseEventProtocolHandler::class)->implementsInterface(ShouldQueueAfterCommit::class));
    }

    #[Test]
    public function it_resolves_the_parser_through_the_application_container(): void
    {
        $this->assertInstanceOf(StandardProtocolParser::class, $this->app->make(ProtocolParser::class));
        $this->assertInstanceOf(ParseEventProtocolHandler::class, $this->app->make(ParseEventProtocolHandler::class));
    }
}
