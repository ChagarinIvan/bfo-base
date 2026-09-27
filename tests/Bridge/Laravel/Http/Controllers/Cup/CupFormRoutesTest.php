<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Auth\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CupFormRoutesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function legacy_cup_form_routes_are_removed(): void
    {
        /** @var Authenticatable $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->get('/cups/create')->assertNotFound();
        $this->post('/cups/store')->assertNotFound();
        $this->get('/cups/101/edit')->assertNotFound();
        $this->post('/cups/101/update')->assertNotFound();
    }
}
