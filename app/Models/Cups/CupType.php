<?php

namespace App\Models\Cups;

class CupType
{
    public const ELITE = 'elite';
    public const MASTER = 'master';
    public const SPRINT = 'sprint';
    public const BIKE = 'bike';
    public const JUNIORS = 'juniors';
    public const YOUTH = 'youth';
    public const SKI = 'ski';

    public const ELK_PATH = 'elk_path';

    public const CLASS_MAP = [
        self::SPRINT => SprintCupType::class,
        self::ELITE => EliteCupType::class,
        self::MASTER => MasterCupType::class,
        self::BIKE => BikeCupType::class,
        self::JUNIORS => JuniorCupType::class,
        self::YOUTH => YouthCupType::class,
        self::SKI => SkiCupType::class,
        self::ELK_PATH => SkiCupType::class,
    ];

    public static function getCupTypeClass(string $cupType): CupTypeInterface
    {
        $class = self::CLASS_MAP[$cupType];
        return app($class);
    }

    /**
     * @return CupTypeInterface[]
     */
    public static function getCupTypes(): array
    {
        $types = [];
        foreach (self::CLASS_MAP as $id => $cupTypeClass) {
            $types[] = self::getCupTypeClass($id);
        }
        return $types;
    }
}
