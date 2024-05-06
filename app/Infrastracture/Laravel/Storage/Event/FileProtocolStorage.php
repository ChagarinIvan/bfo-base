<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Storage\Event;

use App\Domain\Event\Protocol;
use App\Domain\Event\ProtocolStorage;
use Illuminate\Contracts\Filesystem\Filesystem;
use function array_pop;
use function explode;

final readonly class FileProtocolStorage implements ProtocolStorage
{
    public function __construct(
        protected Filesystem $storage,
    ) {
    }

    public function put(string $path, Protocol $protocol): void
    {
        $this->storage->put($path, $protocol->content);
    }

    public function get(string $path): Protocol
    {
        $content = $this->storage->get($path);
        $data = explode('@@', $path);
        $extension = array_pop($data);

        return new Protocol($content, $extension);
    }

    public function delete(string $path): void
    {
        $this->storage->delete($path);
    }
}
