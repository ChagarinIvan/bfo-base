<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\AbstractDto;

final class ActivationDto extends AbstractDto
{
    public string $date;

    public static function requestValidationRules(): array
    {
        return [
            'date' => 'required|date',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->date = $data['date'];

        return $this;
    }
}
