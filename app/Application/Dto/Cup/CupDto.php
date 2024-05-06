<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;
use App\Domain\Cup\CupType;
use App\Models\Year;
use Illuminate\Validation\Rules\Enum;

final class CupDto extends AbstractDto
{
    public string $name;
    public int $eventsCount;
    public int $year;
    public string $type;
    public bool $visible = true;

    public static function requestValidationRules(): array
    {
        return [
            'name' => 'required|max:255',
            'eventsCount' => 'required|numeric|min:1|max:100',
            'year' => [new Enum(Year::class)],
            'type' => [new Enum(CupType::class)],
            'visible' => 'bool',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->name = $data['name'];
        $this->eventsCount = (int) $data['eventsCount'];
        $this->year = (int) $data['year'];
        $this->type = $data['type'];
        $this->visible = isset($data['visible']) ? (bool) $data['visible'] : $this->visible;

        return $this;
    }
}
