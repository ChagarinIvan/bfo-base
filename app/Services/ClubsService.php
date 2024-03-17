<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Club\Club;
use App\Repositories\ClubsRepository;
use Illuminate\Support\Collection;
use function mb_strtolower;
use function preg_replace;
use function str_replace;

class ClubsService
{
    private const EDIT_MAP = [
        'ко ' => ['ка ', 'oc '],
        'ксо ' => ['кса '],
        'бгу' => ['бду', 'bsu'],
    ];

    public static function normalizeName(string $clubName): string
    {
        $clubName = mb_strtolower($clubName);
        $clubName = str_replace(['\'', '"', '«', '»'], '', $clubName);
        $clubName = preg_replace('#\s+#', ' ', $clubName);
        //Исправляем символы
        foreach (ProtocolLineIdentService::SYMBOL_MAP as $symbol => $analogs) {
            $clubName = str_replace($analogs, $symbol, $clubName);
        }

        foreach (self::EDIT_MAP as $name => $analogs) {
            $clubName = str_replace($analogs, $name, $clubName);
        }

        return $clubName;
    }
    public function __construct(private readonly ClubsRepository $repository)
    {
    }

    /**
     * @return Collection|Club[]
     */
    public function getAllClubs(): Collection
    {
        return Club::all();
    }

    public function findClub(string $clubName): ?Club
    {
        return $this->repository->findByNormalizeName(self::normalizeName($clubName));
    }
}
