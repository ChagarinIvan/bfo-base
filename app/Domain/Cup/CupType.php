<?php

declare(strict_types=1);

namespace App\Domain\Cup;

use App\Domain\Cup\CupType\BikeCupType;
use App\Domain\Cup\CupType\CupTypeInterface;
use App\Domain\Cup\CupType\EliteCupType;
use App\Domain\Cup\CupType\ElkPathCup;
use App\Domain\Cup\CupType\JuniorCupType;
use App\Domain\Cup\CupType\MasterCupType;
use App\Domain\Cup\CupType\NewMasterCupType;
use App\Domain\Cup\CupType\SkiCupType;
use App\Domain\Cup\CupType\SprintCupType;
use App\Domain\Cup\CupType\YouthCupType;
use function array_map;

enum CupType: string
{
    /** @return string[] */
    public static function toArray(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public function instance(): CupTypeInterface
    {
        return app(match ($this) {
            self::SPRINT => SprintCupType::class,
            self::ELITE => EliteCupType::class,
            self::MASTER => MasterCupType::class,
            self::BIKE => BikeCupType::class,
            self::JUNIORS => JuniorCupType::class,
            self::YOUTH => YouthCupType::class,
            self::SKI => SkiCupType::class,
            self::ELK_PATH => ElkPathCup::class,
            self::NEW_MASTER => NewMasterCupType::class,
        });
    }

    case ELITE = 'elite';
    case MASTER = 'master';
    case SPRINT = 'sprint';
    case BIKE = 'bike';
    case JUNIORS = 'juniors';
    case YOUTH = 'youth';
    case NEW_MASTER = 'new_master';
    case SKI = 'ski';
    case ELK_PATH = 'elk_path';
}
