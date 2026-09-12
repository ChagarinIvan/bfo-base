<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventDto extends AbstractDto
{
    public EventInfoDto $info;

    public static function requestValidationRules(): array
    {
        return [
            ...EventInfoDto::requestValidationRules(),
        ];
    }

    public function fromArray(array $data): self
    {
        $this->info = new EventInfoDto();
        $this->info = $this->info->fromArray($data);

        return $this;
    }
}
