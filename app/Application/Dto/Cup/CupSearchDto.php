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
        ];
    }
    public function __construct(public ?string $year = null)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('year', $data);

        return $this;
    }
}
