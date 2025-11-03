<?php

declare(strict_types=1);

namespace App\Application\Service\Cup;

use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\ViewCupDto;
use App\Domain\Cup\CupRepository;

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

        for ($i = 1; $i <= 13; $i++) {
            dump($i);
            try {
                $views[$i] = $this->assembler->toViewCupDto($all[$i]);
            } catch (\Throwable $e) {
                dump("ERROR on index $i: " . $e::class . ' — ' . $e->getMessage());
                dump($all[$i]);
                break;
            }
            dump($views[$i]->name);
        }

        dd($views);
        return $views;
    }
}
