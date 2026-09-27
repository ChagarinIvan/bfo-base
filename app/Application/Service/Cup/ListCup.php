<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\SearchCupDto;
use App\Domain\Shared\Criteria;

final readonly class ListCup
{
    public function __construct(
        private SearchCupDto $search,
        private bool $authenticated,
    ) {
    }

    public function criteria(): Criteria
    {
        $visible = true;
        if ($this->search->ids !== null) {
            $visible = null;
        } elseif ($this->authenticated) {
            $visible = match ($this->search->visible) {
                true => true,
                false => false,
                default => null,
            };
        }

        $params = [];
        if ($this->search->ids !== null) {
            $params['ids'] = $this->search->ids;
        }
        if ($this->search->year !== null) {
            $params['year'] = (int) $this->search->year;
        }
        if ($this->search->name !== null && $this->search->name !== '') {
            $params['name'] = $this->search->name;
        }
        if ($visible !== null) {
            $params['visible'] = $visible;
        }

        return new Criteria($params);
    }
}
