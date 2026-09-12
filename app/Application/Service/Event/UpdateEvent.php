<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Dto\Event\UpdateEventDto;
use App\Domain\Event\EventInfo;
use App\Domain\Event\Protocol\ProtocolSource;
use App\Domain\Event\UpdateInput;
use Carbon\Carbon;

final readonly class UpdateEvent
{
    public function __construct(
        private string $id,
        private UpdateEventDto $dto,
        private UserId $userId,
    ) {
    }

    public function id(): int
    {
        return (int) $this->id;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }

    public function input(): UpdateInput
    {
        return new UpdateInput($this->info());
    }

    public function protocolSource(): ?ProtocolSource
    {
        $protocol = $this->dto->protocol;

        return $protocol instanceof EventProtocolDto
            ? new ProtocolSource($protocol->protocol, $protocol->url)
            : null;
    }

    private function info(): EventInfo
    {
        return new EventInfo(
            name: $this->dto->info->name,
            description: $this->dto->info->description,
            date: Carbon::parse($this->dto->info->date),
        );
    }
}
