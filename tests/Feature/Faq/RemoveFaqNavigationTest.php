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

final class RemoveFaqNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RefreshDatabaseState::$migrated = false;
    }

    #[Test]
    public function guest_does_not_see_faq_navigation(): void
    {
        $this->get('/competitions?year=2021')
            ->assertStatus(Response::HTTP_OK)
            ->assertDontSee('/faq')
            ->assertDontSee('apiDropdown')
        ;
    }

    #[Test]
    public function authenticated_user_does_not_see_faq_navigation(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/competitions?year=2021')
            ->assertStatus(Response::HTTP_OK)
            ->assertDontSee('/faq')
            ->assertDontSee('apiDropdown')
        ;
    }
}
