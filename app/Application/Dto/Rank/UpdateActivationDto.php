<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\AbstractDto;

final class UpdateActivationDto extends AbstractDto
{
    public ?string $date = null;

    public static function requestValidationRules(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('date', $data);

        return $this;
    }
}
