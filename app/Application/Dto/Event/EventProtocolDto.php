<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EventProtocolDto extends AbstractDto
{
    public ?UploadedFile $protocol = null;
    public ?string $url = null;

    public static function requestValidationRules(): array
    {
        return [
            'protocol' => 'required_without_all:url',
            'url' => 'required_without_all:protocol',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->protocol = $data['protocol'] ?? null;
        $this->url = $data['url'] ?? null;

        return $this;
    }
}
