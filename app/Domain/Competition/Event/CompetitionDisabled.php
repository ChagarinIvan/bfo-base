<?php

declare(strict_types=1);

namespace App\Domain\Competition\Event;

use App\Domain\Competition\Competition;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class CompetitionDisabled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Competition $competition)
    {
    }
}
