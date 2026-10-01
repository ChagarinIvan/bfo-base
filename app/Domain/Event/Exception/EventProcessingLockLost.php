<?php

declare(strict_types=1);

namespace App\Domain\Event\Exception;

use DomainException;
use RuntimeException;

final class EventProcessingLockLost extends DomainException
{
}
