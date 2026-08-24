<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Event\Event;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\ProtocolStorage;

final readonly class StoreProtocolEventFactory implements EventFactory
{
    public function __construct(
        private EventFactory $decorated,
        private ProtocolStorage $storage,
        private ProtocolPathResolver $path,
    ) {
    }

    public function create(EventInput $input): Event
    {
        $path = $this->path->fromInput($input);
        $this->storage->put($path, $input->protocol);

        return $this->decorated->create($input->withFile($path));
    }
}
