<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckAssembler;
use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Application\Service\RankCheck\Exception\InvalidRankCheckList;
use App\Domain\RankCheck\Exception\UnableToCreate;
use App\Domain\RankCheck\Factory\RankCheckFactory;
use App\Domain\RankCheck\Factory\RankCheckInput;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Storage;
use App\Domain\Shared\UuidGenerator;

final readonly class CreateRankCheckService
{
    public function __construct(
        private RankCheckRepository $checks,
        private Storage $storage,
        private UuidGenerator $uuidGenerator,
        private RankCheckFactory $factory,
        private RankCheckAssembler $assembler,
    ) {
    }

    /** @throws InvalidRankCheckList */
    public function execute(CreateRankCheck $command): ViewRankCheckDto
    {
        $path = 'rank-checks/' . $this->uuidGenerator->generate() . '.csv';

        try {
            $check = $this->factory->create(new RankCheckInput(
                $command->userId->id,
                $path,
                $command->content(),
                $command->extension(),
            ));
        } catch (UnableToCreate $exception) {
            throw new InvalidRankCheckList($exception);
        }

        $this->storage->put($path, $command->content());
        $this->checks->add($check);

        return $this->assembler->toViewRankCheckDto($check);
    }
}
