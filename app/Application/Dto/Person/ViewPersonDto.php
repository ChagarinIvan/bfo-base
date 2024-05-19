<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewPersonDto
{
    public function __construct(
        public string $id,
        public string $lastname,
        public string $firstname,
        public ?string $birthday,
        public ?string $clubId,
        public int $eventsCount,
        public ImpressionDto $created,
        public ImpressionDto $updated,
        // TODO remove
        public ?string $lastPaymentDate,
        /** @var array<string, ViewPersonProtocolLineDto[]> */
        public array $groupedByYearProtocolLines = [],
    ) {
    }
}
