<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent\Exception;

use DomainException;

final class CupAlreadyContainsEvent extends DomainException
{
}
