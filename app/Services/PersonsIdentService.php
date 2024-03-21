<?php

declare(strict_types=1);

namespace App\Services;
use function implode;
use function mb_strtolower;

class PersonsIdentService
{
    /**
     * Создаём идентификационную строку из фамилии имени и года
     *
     * @param string $lastname
     * @param string $firstname
     * @param int|null $year
     *
     * @return string
     */
    public static function makeIdentLine(string $lastname, string $firstname, ?int $year): string
    {
        $data = [
            ProtocolLineIdentService::prepareLine(mb_strtolower($lastname)),
            ProtocolLineIdentService::prepareLine(mb_strtolower($firstname)),
        ];
        if ($year !== null) {
            $data[] = $year;
        }
        return implode('_', $data);
    }
    public function __construct(
        private readonly PersonPromptService $promptService,
        private readonly ProtocolLineIdentService $protocolLineIdentService
    ) {
    }

    /**
     * Поиск соответствующих людей в базе по предварительно подготовленным строкам (makeIdentLine).
     * В ответе [prepared_line => person_id, ] для найденных людей
     *
     * @return array<string, int>
     */
    public function identLines(array $lines): array
    {
        //ищем людей по прямому совпадению подготовленный имён
        $linePersons = $this->promptService->identPersonsByPrompts($lines);
        dd($linePersons);
        //определяем у кого нет совпадения и прогоняем их через identPerson
        foreach ($lines as $preparedLine) {
            if (!isset($linePersons[$preparedLine])) {
                $personId = $this->protocolLineIdentService->identPerson($preparedLine);
                if ($personId > 0) {
                    $linePersons[$preparedLine] = $personId;
                }
            }
        }

        return $linePersons;
    }
}
