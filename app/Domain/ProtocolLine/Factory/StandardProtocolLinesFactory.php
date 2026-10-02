<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Factory;

use App\Domain\Distance\Distance;
use App\Domain\Distance\DistanceFactory;
use App\Domain\Distance\DistanceInput;
use App\Domain\Distance\DistanceRepository;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\Group\GroupFactory;
use App\Domain\Group\GroupInfo;
use App\Domain\Group\GroupInput;
use App\Domain\Group\GroupRepository;
use App\Domain\ProtocolLine\Exception\UnableToCreateProtocolLine;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\Shared\Criteria;
use Throwable;

final class StandardProtocolLinesFactory implements ProtocolLinesFactory
{
    /** @var array<string, Group> */
    private array $groupCache = [];

    /** @var array<string, Distance> */
    private array $distanceCache = [];

    public function __construct(
        private readonly GroupRepository $groups,
        private readonly GroupFactory $groupFactory,
        private readonly DistanceRepository $distances,
        private readonly DistanceFactory $distanceFactory,
    ) {
    }

    /**
     * @throws UnableToCreateProtocolLine
     *
     * @param list<ProtocolLineInput> $inputs
     * @return list<ProtocolLine>
     */
    public function create(Event $event, array $inputs): array
    {
        $protocolLines = [];

        try {
            foreach ($inputs as $input) {
                $group = $this->getGroup($event, $input);
                $distance = $this->getDistance($event, $group, $input);

                $protocolLines[] = $this->createLine($event, $input, $distance->id);
            }
        } catch (Throwable $e) {
            throw new UnableToCreateProtocolLine($e->getMessage());
        }

        return $protocolLines;
    }

    private function getGroup(Event $event, ProtocolLineInput $input): Group
    {
        if (!isset($this->groupCache[$input->normalizedGroupName])) {
            $group = $this->groups->oneByCriteria(new Criteria(['normalizedName' => $input->normalizedGroupName]));

            if ($group === null) {
                $group = $this->groupFactory->create(new GroupInput(
                    info: new GroupInfo(name: $input->group, normalizeName: $input->normalizedGroupName),
                    userId: $event->created_by,
                ));

                $this->groups->add($group);
            }

            $this->groupCache[$input->normalizedGroupName] = $group;
        }

        return $this->groupCache[$input->normalizedGroupName];
    }

    private function getDistance(Event $event, Group $group, ProtocolLineInput $input): Distance
    {
        if (!isset($this->distanceCache[$group->id])) {
            $distance = $this->distances->lockOneByCriteria(new Criteria([
                'eventId' => $event->id,
                'groupId' => $group->id,
                'length' => $input->distanceLength,
                'points' => $input->distancePoints,
            ]));

            if ($distance === null) {
                $distance = $this->distanceFactory->create(new DistanceInput(
                    eventId: $event->id,
                    groupId: $group->id,
                    length: $input->distanceLength,
                    points: $input->distancePoints,
                ));

                $this->distances->add($distance);
            }

            $this->distanceCache[$group->id] = $distance;
        }

        return $this->distanceCache[$group->id];
    }

    private function createLine(Event $event, ProtocolLineInput $input, int $distanceId): ProtocolLine
    {
        $line = new ProtocolLine();
        $line->serial_number = $input->serialNumber;
        $line->lastname = $input->lastname;
        $line->firstname = $input->firstname;
        $line->club = $input->club;
        $line->year = $input->year;
        $line->rank = $input->rank ?? '';
        $line->runner_number = $input->runnerNumber;
        $line->time = $input->time;
        $line->place = $input->place;
        $line->complete_rank = $input->completeRank ?? '';
        $line->points = $input->points;
        $line->vk = $input->vk;
        $line->distance_id = $distanceId;
        $line->prepared_line = $input->preparedLine;
        $line->person_id = null;
        $line->activate_rank = $input->activateRank ? $event->date : null;

        return $line;
    }
}
