<?php

declare(strict_types=1);

namespace App\Application\Dto\Club;

use App\Application\Dto\AbstractDto;

final class ClubDto extends AbstractDto
{
    public string $name;

    public static function validationRules(): array
    {
        return [
            'name' => 'required|max:255',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];

        return $this;
    }
}
