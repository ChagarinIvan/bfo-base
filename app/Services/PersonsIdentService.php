<?php

namespace App\Services;

use App\Models\ProtocolLine;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;

class PersonsIdentService
{
    public function __construct(
        private PersonPromptService $promptService,
        private CacheManager $cache,
    ) {}

    /**
     * В ответе [prepared_line => person_id, ] для найденных людей
     * @param Collection $lines
     * @return Collection
     */
    public function identLines(Collection $lines): Collection
    {
        return $this->cache->remember(
            md5($lines->implode('prepared_line', '_')),
            36000,
            function () use ($lines) {
                //ищем людей по прямому совпадению подготовленный имён
                $linePersons = $this->promptService->identPersonsByPrompts($lines->keys());

                //определяем у кого нет совпадения и прогоняем их через identPerson
                foreach ($lines as $preparedLine => $lineData) {
                    if (!$linePersons->has($preparedLine)) {
                        $personId = ProtocolLineIdentService::identPerson($preparedLine);
                        if ($personId > 0) {
                            $linePersons->put($preparedLine, $personId);
                        }
                    }
                }

                return $linePersons;
            }
        );
    }

    public function preparedLines(Collection $lines): Collection
    {
        return $lines->transform(function(array $line) {
            [$lastname, $firstname] = explode(' ', $line['name']);
            $line['prepared_line'] = ProtocolLine::makeIdentLine($lastname, $firstname, empty($line['year']) ? null : (int)$line['year']);

            return $line;
        })
            ->keyBy('prepared_line');
    }
}
