<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Auth\Impression;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\Event\Event;
use App\Domain\Person\Citizenship;
use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\Factory\PersonInput;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRepository;
use App\Domain\PersonPrompt\PromptIdentifier;
use App\Domain\ProtocolLine\Exception\IdentifyingError;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;

final readonly class StandardProtocolLineIdentifier implements ProtocolLineIdentifier
{
    public function __construct(
        private ProtocolLineOperations $operations,
        private ProtocolLineRepository $protocolLines,
        private PromptIdentifier $promptIdentifier,
        private PersonFactory $personFactory,
        private PersonRepository $persons,
        private ClubRepository $clubs,
        private ClubNameNormalizer $clubNameNormalizer,
    ) {
    }

    public function identify(Event $event, Impression $impression): void
    {
        $this->operations->fastIdentByEvent($event);

        $lines = $this->protocolLines->lockByCriteria(new Criteria([
            'eventId' => $event->id,
            'unidentified' => true,
        ]));

        $personIds = [];

        foreach ($lines as $line) {
            $personId = $personIds[$line->prepared_line] ??= $this->promptIdentifier->identPerson($line->prepared_line)
                ?? $this->createPerson($line, $impression);

            $line->setPerson($personId, $impression);
        }

        $this->protocolLines->update(...$lines);

        $this->operations->activateEventLines($event);
    }

    private function createPerson(ProtocolLine $line, Impression $impression): int
    {
        $info = new PersonInfo(
            $line->firstname,
            $line->lastname,
            $line->year === null ? null : Carbon::create($line->year),
            Citizenship::BELARUS,
            $this->clubs->oneByNormalizedName($this->clubNameNormalizer->normalize($line->club))?->id,
        );

        try {
            $person = $this->personFactory->create(new PersonInput($info, false, $impression->by));
        } catch (PersonInfoAlreadyExist $e) {
            throw new IdentifyingError('Person creation error: ' . $e->getMessage(), 0, $e);
        }

        $this->persons->add($person);

        return $person->id;
    }
}
