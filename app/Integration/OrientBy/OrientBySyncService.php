<?php

declare(strict_types=1);

namespace App\Integration\OrientBy;

use App\Application\Dto\PersonPayment\PersonPaymentDto;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPayments;
use App\Application\Service\PersonPayment\CreateOrUpdatePersonPaymentsService;
use App\Models\Person;
use App\Models\Rank;
use App\Models\Year;
use App\Services\ClubsService;
use App\Services\PersonsIdentService;
use App\Services\PersonsService;
use App\Services\RankService;
use Carbon\Carbon;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use function array_keys;
use function count;
use function sprintf;

class OrientBySyncService
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly PersonsIdentService                 $identService,
        private readonly PersonsService                      $personsService,
        private readonly RankService                         $rankService,
        private readonly ClubsService                        $clubsService,
        private readonly CreateOrUpdatePersonPaymentsService $addPersonPaymentsService,
        LogManager                                           $loggerManager,
    ) {
        $this->logger = $loggerManager->channel('sync');
    }

    /**
     * @param OrientByPersonDto[] $persons
     */
    public function synchronize(array $persons): void
    {
        $this->logger->info('Start synchronisation.');
        $this->logger->info(sprintf("Need process %d persons.", count($persons)));
        $year = Year::actualYear();

        $personsPrompts = [];
        foreach ($persons as $personDto) {
            $personsPrompts[$this->makePromptFromPersonDto($personDto)] = $personDto;
        }

        $indicatedPersons = $this->identService->identLines(array_keys($personsPrompts));
        foreach ($personsPrompts as $personsPrompt => $personDto) {
            $this->logger->info("Process $personDto->name");

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
//                    continue;
//                    $person->firstname = $firstname;
                }

                $date = $personDto->getYear();
                if ($date && (($person->birthday && !$person->birthday->eq($date)) || $person->birthday === null)) {
                    $this->logger->info(
                        "update birthday: " . ($logPerson->birthday ? $logPerson->birthday->format('Y') : '') . " => {$date->format('Y')}",
                        ['person_id' => $personId]
                    );
                    continue;
//                    $person->birthday = $date;
                }

                if ($personDto->paid && $personDto->paymentDate()) {
                    $this->logger->info('update payment: ', ['person_id' => $personId]);

                    $this->addPersonPaymentsService->execute(new CreateOrUpdatePersonPayments(new PersonPaymentDto(
                        personId: (string) $personId,
                        year: (string) $year->value,
                        date: $personDto->paymentDate()->format('Y-m-d'),
                    )));
                }

                if ($this->setClub($person, $personDto)) {
                    $this->logger->info(
                        "update club: " . ($logPerson->club->name ?? '') . " => {$personDto->club}",
                        ['person_id' => $personId]
                    );
                }

                $this->personsService->storePerson($person);
            } else {
                $this->logger->info(
                    "new person: {$personDto->getFirstName()} {$personDto->getLastName()}",
                );

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

                if ($personDto->paid && $personDto->paymentDate()) {
                    $this->addPersonPaymentsService->execute(new CreateOrUpdatePersonPayments(new PersonPaymentDto(
                        personId: (string) $person->id,
                        year: (string) $year->value,
                        date: $personDto->paymentDate()->format('Y-m-d'),
                    )));
                }
            }
        }
        $this->logger->info('Finish synchronisation.');
    }

    private function makePromptFromPersonDto(OrientByPersonDto $personDto): string
    {
        return PersonsIdentService::makeIdentLine($personDto->getLastName(), $personDto->getFirstName(), $personDto->yob);
    }

    private function setClub(Person $person, OrientByPersonDto $personDto): bool
    {
        if ($personDto->club) {
            $club = $this->clubsService->findClub($personDto->club);
            if ($club && $person->club_id !== $club->id) {
                $person->club_id = $club->id;
                return true;
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
            $rank->activated_date = $rank->start_date;

            $this->rankService->storeRank($rank);
        }
    }
}
