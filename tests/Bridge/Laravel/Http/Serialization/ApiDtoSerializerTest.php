<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Serialization;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Bridge\Laravel\Http\Serialization\ApiDtoSerializer;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Rank\Rank;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ApiDtoSerializerTest extends TestCase
{
    #[Test]
    public function backed_enums_serialize_to_their_wire_values_instead_of_reflection_fields(): void
    {
        $serializer = new ApiDtoSerializer();
        $this->assertSame(['trigger' => 'manual', 'rankId' => 7], $serializer->serialize(
            ['trigger' => RankAuditTrigger::Manual, 'rankId' => Rank::CandidateMaster],
            'authenticated',
        ));
    }

    #[Test]
    public function public_group_hides_audit_fields(): void
    {
        $dto = $this->dto();

        $this->assertArrayNotHasKey('created', new ApiDtoSerializer()->serialize($dto, 'public'));
        $this->assertArrayHasKey('created', new ApiDtoSerializer()->serialize($dto, 'authenticated'));
    }

    private function dto(): ViewCompetitionDto
    {
        $impression = new ImpressionDto('2026-08-29T00:00:00+00:00', '10');

        return new ViewCompetitionDto('42', 'Test', 'Description', '2026-08-29', '2026-08-29', 2026, false, $impression, $impression);
    }
}
