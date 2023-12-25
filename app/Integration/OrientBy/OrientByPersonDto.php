<?php

declare(strict_types=1);

namespace App\Integration\OrientBy;

use Carbon\Carbon;
use function preg_split;

class OrientByPersonDto
{
    public function __construct(
        private readonly string $name, //Фамилия Имя             "Ванькевич Дмитрий"
        public readonly ?int $yob,    //год рождения            "1973
        public readonly ?string $club, //клуб                    "КСО «Березино»"
        public readonly ?string $rank, //разряд                  "I"
        public readonly bool $paid     //оплачен ли взнос        "true"
    ) {
    }

    public function getFirstName(): string
    {
        $name = preg_split('#\s+#u', $this->name);

        return $name[1] ?? '';
    }

    public function getLastName(): string
    {
        $name = preg_split('#\s+#u', $this->name);

        return $name[0] ?? '';
    }

    public function getYear(): ?Carbon
    {
        if ($this->yob) {
            $date = Carbon::createFromFormat('Y', (string)$this->yob)->startOfYear();
            return $date === false ? null : $date;
        } else {
            return null;
        }
    }
}
