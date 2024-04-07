<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Storage\Event;

use App\Domain\Event\ProtocolStorage;
use Illuminate\Contracts\Filesystem\Filesystem;

final readonly class FileProtocolStorage implements ProtocolStorage
{
    public function __construct(
        protected Filesystem $storage,
    ) {
    }

    public function put(string $input, string $content): void
    {
        $this->storage->put($input, $content);
    }

    public function get(string $path): string
    {
        return $this->storage->get($path);
    }
}
