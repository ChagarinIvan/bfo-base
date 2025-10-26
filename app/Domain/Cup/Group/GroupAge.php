<?php

declare(strict_types=1);

namespace App\Domain\Cup\Group;

enum GroupAge: int
{
    public function next(): self
    {
        return match ($this) {
            self::a12 => self::a14,
            self::a14 => self::a16,
            self::a16 => self::a18,
            self::a18 => self::a20,
            self::a20 => self::a21,
            self::a21 => self::a35,
            self::a35 => self::a40,
            self::a40 => self::a45,
            self::a45 => self::a50,
            self::a50 => self::a55,
            self::a55 => self::a60,
            self::a60 => self::a65,
            self::a65 => self::a70,
            self::a70 => self::a75,
            default => self::a80,
        };
    }

    public function prev(): self
    {
        return match ($this) {
            self::a16 => self::a14,
            self::a18 => self::a16,
            self::a20 => self::a18,
            self::a21 => self::a20,
            self::a35 => self::a21,
            self::a40 => self::a35,
            self::a45 => self::a40,
            self::a50 => self::a45,
            self::a55 => self::a50,
            self::a60 => self::a55,
            self::a65 => self::a60,
            self::a70 => self::a65,
            self::a75 => self::a70,
            self::a80 => self::a75,
            default => self::a12,
        };
    }

    public function toString(): string
    {
        return (string)($this->value ?? '');
    }

    case a12 = 12;
    case a14 = 14;
    case a16 = 16;
    case a18 = 18;
    case a20 = 20;
    case a21 = 21;
    case a35 = 35;
    case a40 = 40;
    case a45 = 45;
    case a50 = 50;
    case a55 = 55;
    case a60 = 60;
    case a65 = 65;
    case a70 = 70;
    case a75 = 75;
    case a80 = 80;
}
