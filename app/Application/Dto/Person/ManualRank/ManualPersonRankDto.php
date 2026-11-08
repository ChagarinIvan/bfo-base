<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\ManualRank;

use App\Application\Dto\AbstractDto;

final class ManualPersonRankDto extends AbstractDto
{
    public int $rankId;
    public string $achievedOn;
    public string $activatedOn;

    public static function requestValidationRules(): array
    {
        return [
            'rankId' => 'required|integer|between:1,9',
            'achievedOn' => 'required|date_format:Y-m-d|before_or_equal:today',
            'activatedOn' => 'required|date_format:Y-m-d|after_or_equal:achievedOn',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->rankId = (int) $data['rankId'];
        $this->achievedOn = (string) $data['achievedOn'];
        $this->activatedOn = (string) $data['activatedOn'];
        return $this;
    }
}
