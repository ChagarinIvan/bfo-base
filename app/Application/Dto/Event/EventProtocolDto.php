<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;
use App\Bridge\Laravel\Http\Controllers\Event\UploadHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class EventProtocolDto extends AbstractDto
{
    use UploadHelper;

    public string $content;

    public string $extension;

    public static function requestValidationRules(): array
    {
        return [
            'protocol' => 'required_without_all:url',
            'url' => 'required_without_all:protocol',
        ];
    }

    public function fromArray(array $data): self
    {
        if (array_key_exists('protocol', $data)) {
            /** @var UploadedFile $protocol */
            $protocol = $data['protocol'];
            $this->extension = $protocol->getMimeType();
            $this->content = $protocol->getContent();
        } else {
            $url = $data['url'];
            $protocol = $this->uploadProtocol($url);
            $this->content = $protocol->content;
            $this->extension = $protocol->extension;
        }

        dd($this);
        return $this;
    }
}
