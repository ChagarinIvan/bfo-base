<?php

declare(strict_types=1);

namespace Tests\Feature\Faq;

use App\Domain\Auth\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class RemovedFaqRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RefreshDatabaseState::$migrated = false;
    }

    #[Test]
    public function guest_cannot_open_removed_faq_routes(): void
    {
        $this->get('/faq')
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertDontSeeText('FAQ')
        ;

        $this->get('/faq/api')
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertDontSeeText('API FAQ')
        ;
    }

    #[Test]
    public function authenticated_user_cannot_open_removed_faq_routes(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/faq')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get('/faq/api')->assertStatus(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function unrelated_competitions_route_remains_available(): void
    {
        $this->get('/competitions?year=2021')
            ->assertStatus(Response::HTTP_OK)
            ->assertDontSee('/faq')
        ;
    }
}
