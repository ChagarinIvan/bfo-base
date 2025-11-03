<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Domain\Cup\CupRepository;
use function array_map;
use function Laravel\Prompts\form;

final readonly class ListCupService
{
    public function __construct(
        private CupRepository $cups,
        private CupAssembler $assembler,
    ) {
    }

    /** @return ViewCupDto[] */
    public function execute(ListCup $command): array
    {
        $all = $this->cups->byCriteria($command->criteria())->all();
        $views = [];

        for ($i = 1; $i <= 11; $i++) {
            $views[$i] = $this->assembler->toViewCupDto($all[$i]);
            dump($views[$i]->name);
        }

        return $views;
    }
}
