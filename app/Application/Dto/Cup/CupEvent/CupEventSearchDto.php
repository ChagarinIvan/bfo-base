<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup\CupEvent;

use App\Application\Dto\AbstractDto;

final class CupEventSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'cupId' => 'numeric',
        ];
    }

    public function __construct(public ?string $cupId = null)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('cupId', $data);

        return $this;
    }
}
