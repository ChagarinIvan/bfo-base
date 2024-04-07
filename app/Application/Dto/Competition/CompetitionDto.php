<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\AbstractDto;

final class CompetitionDto extends AbstractDto
{
    public string $name;
    public string $description;
    public string $from;
    public string $to;

    public static function requestValidationRules(): array
    {
        return [
            'name' => 'required|max:255',
            'description' => 'required|max:255',
            'from' => 'required|date',
            'to' => 'required|date',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->from = $data['from'];
        $this->to = $data['to'];

        return $this;
    }
}
