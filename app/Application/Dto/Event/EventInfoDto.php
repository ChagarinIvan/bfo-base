<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventInfoDto extends AbstractDto
{
    public string $name;

    public ?string $description;

    public string $date;

    public static function validationRules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'max:255',
            'date' => 'required|date',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];
        $this->setStringParam('description', $data);
        $this->date = $data['date'];

        return $this;
    }
}
