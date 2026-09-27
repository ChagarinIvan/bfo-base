<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\RankCheck\Exception\ProcessError;
use App\Domain\RankCheck\Factory\RankCheckRowFactory;
use App\Domain\RankCheck\Factory\RankCheckRowInput;
use App\Domain\Shared\IdentLineGenerator;
use App\Domain\Shared\Storage;
use Exception;
use function array_map;
use function array_values;

final readonly class StandardRankCheckProcessor implements RankCheckProcessor
{
    public function __construct(
        private RankListParser $parser,
        private RankCheckPersonMatcher $matcher,
        private Storage $storage,
        private IdentLineGenerator $identLineGenerator,
        private RankCheckPersonSnapshotReader $people,
        private RankCheckRowFactory $rowFactory,
        private RankCheckRowRepository $rows,
    ) {
    }

    public function process(RankCheck $check): void
    {
        try {
            $lines = $this->parser->parse($this->storage->get($check->source_path), 'csv');

            $prepared = array_map(fn(RankListItem $line): string => $this->identLineGenerator->generate($line->lastname, $line->firstname, $line->year), $lines);

            $matches = $this->matcher->match($prepared);
            $snapshots = $this->people->read(array_values($matches));

            foreach ($lines as $position => $line) {
                $person = isset($matches[$prepared[$position]]) ? ($snapshots[$matches[$prepared[$position]]] ?? null) : null;

                $row = $this->rowFactory->create(new RankCheckRowInput(
                    rankCheckId: $check->id,
                    position: $position + 1,
                    line: $line,
                    person: $person,
                ));

                $this->rows->add($row);
            }
        } catch (Exception $exception) {
            throw new ProcessError($exception);
        }
    }
}
