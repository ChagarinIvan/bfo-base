<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use Carbon\Carbon;

final readonly class PersonExtractor
{
    public function __construct(
        private ClubRepository $clubs,
        private ClubNameNormalizer $clubNameNormalizer,
    ) {
    }

    public function extract(ProtocolLine $protocolLine, Impression $impression): Person
    {
        $person = new Person();
        $person->lastname = $protocolLine->lastname;
        $person->firstname = $protocolLine->firstname;
        $person->birthday = $protocolLine->year === null ? null : Carbon::create((int) $protocolLine->year, 1, 1);
        $person->club_id = $this->clubs->oneByNormalizedName($this->clubNameNormalizer->normalize($protocolLine->club))?->id;
        $person->citizenship = Citizenship::BELARUS;
        $person->from_base = false;
        $person->created = $person->updated = $impression;

        return $person;
    }
}
