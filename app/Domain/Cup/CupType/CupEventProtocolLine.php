<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\Group\CupGroup;
use App\Domain\ProtocolLine\ProtocolLine;

final readonly class CupEventProtocolLine
{
   public function __construct(
       public ProtocolLine $line,
       public CupGroup $calculatedGroup,
       public CupGroup $actualGroup,
   ) {
   }
}
