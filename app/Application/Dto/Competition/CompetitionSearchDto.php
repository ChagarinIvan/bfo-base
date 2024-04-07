<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\AbstractDto;

final class CompetitionSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'year' => 'numeric|digits:4',
        ];
    }
    public function __construct(public ?string $year = null)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('year', $data);

        return $this;
    }
}
