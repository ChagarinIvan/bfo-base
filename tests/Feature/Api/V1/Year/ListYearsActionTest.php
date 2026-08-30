<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Year;

use App\Models\Year;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_map;

final class ListYearsActionTest extends TestCase
{
    #[Test]
    public function it_returns_all_year_enum_values_as_a_direct_array(): void
    {
        $this->getJson('/api/v1/years')
            ->assertOk()
            ->assertExactJson(array_map(
                static fn (Year $year): int => $year->value,
                Year::cases(),
            ))
        ;
    }

    #[Test]
    public function it_is_public_without_authentication(): void
    {
        $this->getJson('/api/v1/years')->assertOk();
    }

    #[Test]
    public function it_accepts_only_get_requests(): void
    {
        $this->postJson('/api/v1/years')->assertMethodNotAllowed();
    }
}
