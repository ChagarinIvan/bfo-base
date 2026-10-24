<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\PersonRankAudit;

use App\Bridge\Laravel\Http\Controllers\Api\V1\Person\RankAudit\ListRankAuditAction;
use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use App\Infrastructure\Sanctum\SanctumUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_keys;

/** @see ListRankAuditAction
 */
final class RankAuditApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function audit_requires_authentication(): void
    {
        $this->getJson('/api/v1/persons/1/rank-audit')->assertUnauthorized();
    }

    #[Test]
    public function operator_can_page_minimal_change_records_and_noop_does_not_add_a_record(): void
    {
        $user = SanctumUser::query()->create(['email' => 'audit@example.test', 'password' => 'secret']);
        Sanctum::actingAs($user);
        $person = Person::factory()->createOne(['current_rank' => Rank::FirstRank]);
        $url = "/api/v1/persons/{$person->id}";
        $this->postJson("{$url}/ranks/rebuild")->assertNoContent();
        $person->refresh();
        $person->current_rank = Rank::FirstRank;
        $person->save();
        $this->postJson("{$url}/ranks/rebuild")->assertNoContent();
        $this->postJson("{$url}/ranks/rebuild")->assertNoContent();
        $response = $this->getJson("{$url}/rank-audit?perPage=1&page=1")
            ->assertOk()->assertJsonCount(1)->assertHeader('X-Pagination-Has-Next', 'true');
        $response->assertJsonPath('0.trigger', 'manual')->assertJsonPath('0.created.by', (string) $user->id);
        $this->assertSame(['id', 'personId', 'trigger', 'created', 'before', 'after'], array_keys($response->json('0')));
        $this->getJson("{$url}/rank-audit?page=2&perPage=1")->assertOk()->assertJsonCount(1);
        $this->getJson('/api/v1/persons/999999/rank-audit')->assertNotFound();
        $this->getJson("{$url}/rank-audit?perPage=0")->assertUnprocessable();
    }
}
