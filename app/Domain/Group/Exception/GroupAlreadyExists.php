<?php

declare(strict_types=1);

namespace App\Domain\Group\Exception;

use DomainException;

final class GroupAlreadyExists extends DomainException
{
}
