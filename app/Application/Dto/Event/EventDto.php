<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventDto extends AbstractDto
{
    public EventInfoDto $info;

    public string $competitionId;

    public static function requestValidationRules(): array
    {
        return [
            ...EventInfoDto::requestValidationRules(),
        ];
    }

    public static function parametersValidationRules(): array
    {
        return [
            'competitionId' => 'required|numeric',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->info = new EventInfoDto();
        $this->info = $this->info->fromArray($data);
        $this->competitionId = $data['competitionId'];

        return $this;
    }
}
