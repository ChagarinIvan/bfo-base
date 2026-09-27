<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

use App\Application\Dto\AbstractDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class RankCheckListDto extends AbstractDto
{
    public ?UploadedFile $list = null;

    public static function requestValidationRules(): array
    {
        return ['list' => ['required', 'file']];
    }

    public function fromArray(array $data): self
    {
        $this->list = $data['list'] ?? null;

        return $this;
    }
}
