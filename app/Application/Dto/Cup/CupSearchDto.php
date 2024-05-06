<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\AbstractDto;
use App\Models\Year;
use Illuminate\Validation\Rules\Enum;

final class CupSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'year' => [new Enum(Year::class)],
            'visible' => 'bool',
        ];
    }
    public function __construct(public ?string $year = null, public bool $visible = true)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('year', $data);
        $this->visible = (bool) ($data['visible'] ?? $this->visible);

        return $this;
    }
}
