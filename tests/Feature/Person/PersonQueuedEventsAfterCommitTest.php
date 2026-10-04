<?php

declare(strict_types=1);

namespace Tests\Feature\Person;

use App\Application\Handler\Person\PersonDisabledHandler;
use App\Application\Handler\PersonPrompt\DeletePersonPromptsOnDisablePersonHandler;
use App\Application\Handler\PersonPrompt\PersonCreatedHandler;
use App\Application\Handler\PersonPrompt\PersonInfoUpdatedHandler;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Person\Event\PersonInfoUpdated;
use App\Domain\Person\Person;
use App\Domain\Shared\AggregatedEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonQueuedEventsAfterCommitTest extends TestCase
{
    /** @return iterable<string, array{class-string<AggregatedEvent>, list<class-string>}> */
    public static function personEvents(): iterable
    {
        yield 'created' => [PersonCreated::class, [PersonCreatedHandler::class]];
        yield 'info updated' => [PersonInfoUpdated::class, [PersonInfoUpdatedHandler::class]];
        yield 'disabled' => [PersonDisabled::class, [PersonDisabledHandler::class, DeletePersonPromptsOnDisablePersonHandler::class]];
    }

    /** @param class-string<AggregatedEvent> $eventClass
     * @param list<class-string> $handlerClasses
     */
    #[Test]
    #[DataProvider('personEvents')]
    public function it_queues_person_handlers_after_commit(string $eventClass, array $handlerClasses): void
    {
        Queue::fake();

        event(new $eventClass(new Person()));

        foreach ($handlerClasses as $handlerClass) {
            Queue::assertPushed(
                CallQueuedListener::class,
                static fn (CallQueuedListener $job): bool => $job->class === $handlerClass && $job->afterCommit === true,
            );
        }
    }
}
