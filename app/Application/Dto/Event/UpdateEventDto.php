<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class UpdateEventDto extends AbstractDto
{
    public EventInfoDto $info;
    public ?EventProtocolDto $protocol = null;

    public static function requestValidationRules(): array
    {
        return [
            ...EventInfoDto::requestValidationRules(),
            'protocol' => '',
            'url' => '',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->info = new EventInfoDto();
        $this->info = $this->info->fromArray($data);
        $this->protocol = new EventProtocolDto();
        $this->protocol = array_key_exists('protocol', $data) || array_key_exists('protocol', $data)
            ? $this->protocol->fromArray($data)
            : null;

        return $this;
    }
}
