<?php

declare(strict_types=1);

namespace App\Infrastracture\Integration\OrientBy;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use InvalidArgumentException;
use function preg_split;

final readonly class OrientByPersonDto
{
    public function __construct(
        public string $name, //Фамилия Имя                "Ванькевич Дмитрий"
        public ?int $yob,    //год рождения                "1973
        public ?string $club, //клуб                       "КСО «Березино»"
        public ?string $rank, //разряд                     "I"
        public bool $paid,  //оплачен ли взнос             "true"
        public ?string $paymentDate,  //когда оплачен взнос "31.12.2023"
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
            /** @var Carbon|false $date */
            $date = Carbon::createFromFormat('Y', (string)$this->yob)->startOfYear();

            return $date === false ? null : $date;
        }

        return null;
    }

    public function paymentDate(): ?Carbon
    {
        if (!$this->paymentDate) {
            return null;
        }

        foreach (['d.m.Y H:i:s', 'd.m.Y'] as $format) {
            $date = Carbon::createFromFormat($format, $this->paymentDate);

            if ($date !== false) {
                return $date;
            }
        }

        throw new InvalidArgumentException("Неверный формат даты: $this->paymentDate");
    }
}
