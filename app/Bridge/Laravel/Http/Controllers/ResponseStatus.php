<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ResponseStatus
{
    public function __construct(public int $status)
    {
    }
}
