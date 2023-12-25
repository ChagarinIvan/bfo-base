<?php
declare(strict_types=1);

namespace App\Collections;

use App\Models\Rank;
use Illuminate\Support\Collection;

/**
 * @method void each(\Closure $closure)
 * @method static merge(static $collection)
 * @method Collection keys()
 * @method Rank|null first()
 * @method void put(int $personId, ?Rank $actualRank)
 */
class RanksCollection extends Collection
{
}
