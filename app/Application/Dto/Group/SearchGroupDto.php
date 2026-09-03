<?php

declare(strict_types=1);

namespace App\Application\Dto\Group;

use App\Application\Dto\AbstractDto;
use function trim;

final class SearchGroupDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return ['name' => 'nullable|string|min:1|max:255', 'excludeId' => 'nullable|integer|min:1'];
    }

    public function __construct(public ?string $name = null, public ?string $excludeId = null)
    {
    }

    public function fromArray(array $data): self
    {
        $this->name = isset($data['name']) ? trim($data['name']) : null;
        $this->excludeId = $data['excludeId'] ?? null;

        return $this;
    }
}
