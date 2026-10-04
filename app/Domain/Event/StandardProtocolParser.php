<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Exception\EventParsingError;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Group\GroupRepository;
use App\Domain\ProtocolLine\ProtocolLineInput;
use App\Domain\Rank\RankNormalizer;
use App\Domain\Shared\IdentLineGenerator;
use App\Domain\Shared\Storage;
use App\Models\Parser\ParserFactory;
use function count;
use function implode;
use function sprintf;
use function str_replace;
use function trim;

final readonly class StandardProtocolParser implements ProtocolParser
{
    public function __construct(
        private Storage $storage,
        private ProtocolPathResolver $path,
        private GroupRepository $groups,
        private RankNormalizer $ranks,
        private IdentLineGenerator $identLines,
        private GroupNameNormalizer $groupNameNormalizer,
    ) {
    }

    /** @return list<ProtocolLineInput> */
    public function parse(Event $event): array
    {
        $content = $this->storage->get($event->file);
        $extension = $this->path->extensionFromPath($event->file);

        $parser = ParserFactory::createProtocolParser(
            $content,
            $this->groups->all()->pluck('normalize_name'),
            $extension,
        );

        $lines = $parser
            ->parse($content)
            ->map($this->toItem(...))
            ->all()
        ;

        if (count($lines) === 0) {
            throw new EventParsingError(sprintf('Parser [%s] error: Parsed 0 lines.', $parser::class));
        }

        return $lines;
    }

    /** @param array<string, mixed> $line */
    private function toItem(array $line): ProtocolLineInput
    {
        $distance = $line['distance'] ?? [];
        $rank = $this->ranks->normalize(isset($line['complete_rank']) ? (string) $line['complete_rank'] : null);
        $lastname = $line['lastname'] ?? throw new EventParsingError('Empty lastname in line ' . implode('', $line));
        $firstname = $line['firstname'] ?? throw new EventParsingError('Empty firstname in line ' . implode('', $line));
        $year = isset($line['year']) ? (int) $line['year'] : null;
        $groupName = $line['group']  ?? throw new EventParsingError('Empty group name in line ' . implode('', $line));
        $normalizedGroupName = $this->groupNameNormalizer->normalize(str_replace(' ', '', $groupName));

        return new ProtocolLineInput(
            serialNumber: (int) ($line['serial_number'] ?? 0),
            lastname: $lastname,
            firstname: $firstname,
            club: trim((string) ($line['club'] ?? '')),
            year: $year,
            rank: trim((string) ($line['rank'] ?? '')),
            runnerNumber: (int) ($line['runner_number'] ?? 0),
            time: isset($line['time']) ? (string) $line['time'] : null,
            place: isset($line['place']) ? (int) $line['place'] : null,
            completeRank: $rank?->label() ?? '',
            points: isset($line['points']) ? (int) $line['points'] : null,
            vk: (bool) ($line['vk'] ?? false),
            group: $groupName,
            normalizedGroupName: $normalizedGroupName,
            distanceLength: (int) ($distance['length'] ?? 0),
            distancePoints: (int) ($distance['points'] ?? 0),
            preparedLine: $this->identLines->generate($lastname, $firstname, $year),
            activateRank: $rank?->isAutomaticallyActivated() ?? false,
        );
    }
}
