<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Cup;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Cup\ListCupsAction;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupType;
use App\Infrastructure\Sanctum\SanctumUser;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @see ListCupsAction */

final class ListCupsActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_a_compact_public_listing_payload(): void
    {
        $cup = $this->createCup([
            'name' => 'Public master cup',
            'events_count' => 4,
        ]);

        $this->getJson('/api/v1/cups')
            ->assertOk()
            ->assertJsonStructure([[
                'id',
                'name',
                'eventsCount',
                'year',
                'type',
                'groups',
                'visible',
            ]])
            ->assertJsonPath('0.id', (string) $cup->getKey())
            ->assertJsonPath('0.eventsCount', '4')
            ->assertJsonMissingPath('0.created')
            ->assertJsonMissingPath('0.updated')
            ->assertJsonMissingPath('0.events')
        ;
    }

    #[Test]
    public function it_includes_audit_fields_for_authenticated_client(): void
    {
        $this->createCup();
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/cups')
            ->assertOk()
            ->assertJsonStructure([['created', 'updated']])
        ;
    }

    #[Test]
    public function anonymous_client_cannot_request_hidden_cups(): void
    {
        $visibleCup = $this->createCup(['visible' => true]);
        $this->createCup(['visible' => false]);

        $this->getJson('/api/v1/cups?visible=0')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $visibleCup->getKey())
        ;
    }

    #[Test]
    public function authenticated_client_can_request_hidden_cups(): void
    {
        $this->createCup(['visible' => true]);
        $hiddenCup = $this->createCup(['visible' => false]);

        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/cups?visible=0')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $hiddenCup->getKey())
        ;
    }

    #[Test]
    public function authenticated_client_can_request_all_cups_without_visibility_filter(): void
    {
        $this->createCup(['visible' => true]);
        $this->createCup(['visible' => false]);
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/cups')
            ->assertOk()
            ->assertJsonCount(2)
        ;
    }

    #[Test]
    public function authenticated_client_can_request_visible_cups(): void
    {
        $visibleCup = $this->createCup(['visible' => true]);
        $this->createCup(['visible' => false]);
        Sanctum::actingAs($this->createUser());

        $this->getJson('/api/v1/cups?visible=1')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $visibleCup->getKey())
        ;
    }

    #[Test]
    public function it_combines_year_and_trimmed_case_insensitive_name_filters(): void
    {
        $matchingCup = $this->createCup([
            'name' => 'Forest Master Cup',
            'year' => Year::y2026,
        ]);
        $this->createCup([
            'name' => 'Forest Master Cup',
            'year' => Year::y2025,
        ]);
        $this->createCup(['name' => 'Brest Cup', 'year' => Year::y2026]);

        $this->getJson('/api/v1/cups?year=2026&name=%20%20mAsTeR%20%20')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', (string) $matchingCup->getKey())
        ;
    }

    #[Test]
    public function it_returns_pagination_headers(): void
    {
        $this->createCup(['id' => 1]);
        $this->createCup(['id' => 2]);
        $this->createCup(['id' => 3]);

        $this->getJson('/api/v1/cups?perPage=2&page=2')
            ->assertOk()
            ->assertHeader('X-Pagination-Has-Next', 'false')
            ->assertHeader('X-Pagination-Per-Page', '2')
            ->assertHeader('X-Pagination-Current-Page', '2')
            ->assertHeaderMissing('X-Pagination-Total')
            ->assertHeaderMissing('X-Pagination-Last-Page')
        ;
    }

    #[Test]
    public function it_rejects_invalid_listing_parameters(): void
    {
        $this->getJson('/api/v1/cups?year=1999')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'year'])
        ;

        $this->getJson('/api/v1/cups?name=ab')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'name'])
        ;

        $this->getJson('/api/v1/cups?page=0&perPage=101')
            ->assertUnprocessable()
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'page'])
            ->assertJsonFragment(['code' => 'validation_error', 'field' => 'perPage'])
        ;
    }

    /** @param array<string, mixed> $attributes */
    private function createCup(array $attributes = []): Cup
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->createOne([
            'id' => ((int) Cup::query()->max('id')) + 1,
            'type' => CupType::MASTER,
            'year' => Year::y2026,
            ...$attributes,
        ]);

        return $cup;
    }

    private function createUser(): SanctumUser
    {
        return SanctumUser::query()->create([
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret'),
        ]);
    }
}
