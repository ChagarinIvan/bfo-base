<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Factory\EventInput;
use Carbon\Carbon;
use Illuminate\Support\Str;

final readonly class ProtocolPathResolver
{
    public function protocolPath(Carbon $date, string $name, string $extension): string
    {
        return "{$date->format('Y')}/{$date->format('Y-m-d')}_" . Str::snake($name) . "@@$extension";
    }

    public function fromInput(EventInput $input): string
    {
        return $this->protocolPath($input->info->date, $input->info->name, $input->protocol->extension);
    }
}
