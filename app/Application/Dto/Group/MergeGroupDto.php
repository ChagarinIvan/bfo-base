<?php

declare(strict_types=1);

namespace App\Application\Dto\Group;

use App\Application\Dto\AbstractDto;

final class MergeGroupDto extends AbstractDto
{
    public string $targetGroupId;

    public static function requestValidationRules(): array
    {
        return ['targetGroupId' => 'required|integer|min:1'];
    }

    public function fromArray(array $data): self
    {
        $this->targetGroupId = $data['targetGroupId'];

        return $this;
    }
}
