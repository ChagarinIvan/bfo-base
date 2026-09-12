<?php

declare(strict_types=1);

namespace App\Domain\Event\Protocol;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class ProtocolSource
{
    public function __construct(
        public ?UploadedFile $file,
        public ?string $url,
    ) {
    }
}
