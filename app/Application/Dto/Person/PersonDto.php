<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use App\Application\Dto\Event\EventInfoDto;

final class PersonDto extends AbstractDto
{
    public PersonInfoDto $info;

    public bool $fromBase = false;

    public static function requestValidationRules(): array
    {
        return [
            ...PersonInfoDto::requestValidationRules(),
            'fromBase' => 'bool',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->info = new PersonInfoDto();
        $this->info = $this->info->fromArray($data);

        $this->fromBase = (bool) ($data['fromBase'] ?? false);

        return $this;
    }
}
