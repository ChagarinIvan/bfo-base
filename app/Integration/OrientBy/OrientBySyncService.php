<?php

declare(strict_types=1);

namespace App\Integration\OrientBy;

use App\Models\Person;
use App\Models\PersonPayment;
use App\Models\Rank;
use App\Services\ClubsService;
use App\Services\PaymentService;
use App\Services\PersonsIdentService;
use App\Services\PersonsService;
use App\Services\RankService;
use Carbon\Carbon;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;

class OrientBySyncService
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly OrientByApiClient $apiClient,
        private readonly PersonsIdentService $identService,
        private readonly PersonsService $personsService,
        private readonly RankService $rankService,
        private readonly ClubsService $clubsService,
        private readonly PaymentService $paymentService,
        LogManager $loggerManager,
    ) {
        $this->logger = $loggerManager->channel('sync');
    }

    public function synchronize(): void
    {
        $persons = $this->apiClient->getPersons();

        $personsPrompts = [];
        foreach ($persons as $personDto) {
            $personsPrompts[self::makePromptFromPersonDto($personDto)] = $personDto;
        }

        $indicatedPersons = $this->identService->identLines(array_keys($personsPrompts), 2);
        foreach ($personsPrompts as $personsPrompt => $personDto) {
            if (isset($indicatedPersons[$personsPrompt])) {
                $personId = (int)$indicatedPersons[$personsPrompt];
                $person = $this->personsService->getPerson($personId);
                $logPerson = $person->replicate();

                $person->from_base = true;

                $lastname = $personDto->getLastName();
                if ($person->lastname !== $lastname) {
                    $this->logger->info(
                        "update lastname: {$logPerson->lastname} => {$lastname}",
                        ['person_id' => $personId]
                    );
                    $person->lastname = $lastname;
                }

                $firstname = $personDto->getFirstName();
                if ($person->firstname !== $firstname) {
                    $this->logger->info(
                        "update firstname: {$logPerson->firstname} => {$firstname}",
                        ['person_id' => $personId]
                    );
                    $person->firstname = $firstname;
                }

                $date = $personDto->getYear();
                if ($date && (($person->birthday && !$person->birthday->eq($date)) || $person->birthday === null)) {
                    $this->logger->info(
                        "update birthday: ".($logPerson->birthday ? $logPerson->birthday->format('Y') : '')." => {$date->format('Y')}",
                        ['person_id' => $personId]
                    );
                    $person->birthday = $date;
                }

                /** @var PersonPayment $lastPayment */
                $lastPayment = $person->payments->last();
                $date = $personDto->getLastPaymentDate();
                if (
                    ($lastPayment === null && $personDto->bfopaydate)
                    || ($lastPayment && !$lastPayment->date->eq($date))
                    && $date
                ) {
                    $this->logger->info(
                        "update payment: " . ($lastPayment ? $lastPayment->date->format('Y-m-d') : '') . " => {$personDto->bfopaydate}",
                        ['person_id' => $personId]
                    );
                    $this->paymentService->addPayment($person->id, $personDto->getLastPaymentDate());
                }

                if ($this->setClub($person, $personDto)) {
                    $this->logger->info(
                        "update club: ".($logPerson->club->name ?? '')." => {$personDto->club}",
                        ['person_id' => $personId]
                    );
                }

                $this->personsService->storePerson($person);
            } else {
                $person = new Person();
                $person->from_base = true;
                $person->lastname = $personDto->getLastName();
                $person->firstname = $personDto->getFirstName();
                $person->birthday = $personDto->getYear();
                $this->setClub($person, $personDto);
                $person = $this->personsService->storePerson($person);

                if ($personDto->rank) {
                    $this->setRank($person->id, $personDto->rank);
                }

                if ($personDto->bfopaydate) {
                    $this->paymentService->addPayment($person->id, $personDto->getLastPaymentDate());
                }
            }
        }
    }

    private function makePromptFromPersonDto(OrientByPersonDto $personDto): string
    {
        return PersonsIdentService::makeIdentLine($personDto->getLastName(), $personDto->getFirstName(), $personDto->yob);
    }

    private function setClub(Person $person, OrientByPersonDto $personDto): bool
    {
        if ($personDto->club) {
            $club = $this->clubsService->findClub($personDto->club);
            if ($club) {
                if ($person->club_id !== $club->id) {
                    $person->club_id = $club->id;
                    return true;
                }
            }
        }
        return false;
    }

    private function setRank(int $personId, ?string $rankData): void
    {
        if (Rank::getRank($rankData)) {
            $rank = new Rank();
            $rank->person_id = $personId;
            $rank->rank = Rank::getRank($rankData);
            $rank->start_date = Carbon::now();
            $rank->finish_date = $rank->start_date->clone()->addYears(2);
            $this->rankService->storeRank($rank);
        }
    }
}