<?php

declare(strict_types=1);

namespace App\Domain\Event\Exception;

use DomainException;

final class EventNotExists extends DomainException
{
}
