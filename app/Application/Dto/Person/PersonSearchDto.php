<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;

final class PersonSearchDto extends AbstractDto
{
    public static function parametersValidationRules(): array
    {
        return [
            'clubId' => 'numeric',
        ];
    }

    public function __construct(public ?string $clubId = null)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('clubId', $data);

        return $this;
    }
}
