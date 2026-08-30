<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class HttpError
{
    public function __construct(public int $status, public string $code)
    {
    }
}
