<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\AbstractDto;

final class CompetitionSearchDto extends AbstractDto
{
    public ?string $year;

    public static function validationRules(): array
    {
        return [
            'year' => 'numeric|digits:4',
        ];
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('year', $data);

        return $this;
    }

    public function fromRequest(): bool
    {
        return false;
    }
}
