<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventSearchDto extends AbstractDto
{
    public static function validationRules(): array
    {
        return [
            'competitionId' => 'numeric',
        ];
    }
    public function __construct(public ?string $competitionId = null)
    {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('competitionId', $data);

        return $this;
    }
}
