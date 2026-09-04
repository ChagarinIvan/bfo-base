<?php

declare(strict_types=1);

namespace App\Application\Dto\Group;

use App\Application\Dto\AbstractDto;

final class GroupDto extends AbstractDto
{
    public string $name;

    public static function requestValidationRules(): array
    {
        return ['name' => 'required|string|max:255'];
    }

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];

        return $this;
    }
}
