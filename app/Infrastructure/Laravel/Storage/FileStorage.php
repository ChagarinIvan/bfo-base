<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Storage;

use App\Domain\Shared\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

final readonly class FileStorage implements Storage
{
    public function __construct(
        private Filesystem $storage,
    ) {
    }

    public function put(string $path, string $content): void
    {
        $this->storage->put($path, $content);
    }

    public function get(string $path): string
    {
        return $this->storage->get($path);
    }

    public function delete(string $path): void
    {
        $this->storage->delete($path);
    }
}
