<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\IdentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParserEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Event */
    private Event $event;

    /**
     * Create a new job instance.
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Execute the job.
     * @param IdentService $indentService
     * @return void
     */
    public function handle(IdentService $indentService): void
    {
        foreach ($this->event->protocolLines as $line) {
            $personId = $indentService->identPerson($line);
            if ($personId > 0) {
                $line->person_id = $personId;
                $line->save();
            }
        }
    }
}
