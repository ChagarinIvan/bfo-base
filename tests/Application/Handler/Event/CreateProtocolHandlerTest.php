<?php

declare(strict_types=1);

namespace Tests\Application\Handler\Event;

use App\Application\Handler\Event\CreateProtocolHandler;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class CreateProtocolHandlerTest extends TestCase
{
    #[Test]
    public function it_is_queued_after_the_event_creation_transaction_commits(): void
    {
        $this->assertTrue(new ReflectionClass(CreateProtocolHandler::class)->implementsInterface(ShouldQueueAfterCommit::class));
    }
}
