<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonsRoutesTest extends TestCase
{
    #[Test]
    public function it_removes_the_legacy_person_list_routes(): void
    {
        $this->get('/persons')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->getJson('/api/person')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->getJson('/api/persons')->assertStatus(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function it_removes_the_legacy_person_view_route(): void
    {
        $this->get('/persons/101/show')->assertStatus(Response::HTTP_NOT_FOUND);
    }

    #[Test]
    public function it_removes_legacy_person_payment_routes(): void
    {
        $this->get('/persons/101/payments')->assertNotFound();
        $this->get('/persons/101/payments/create')->assertNotFound();
        $this->post('/persons/101/payments/store')->assertNotFound();
    }

    #[Test]
    public function it_removes_the_remaining_legacy_person_routes(): void
    {
        $this->get('/persons/101/delete')->assertNotFound();
        $this->get('/persons/person/101/show')->assertNotFound();
        $this->get('/persons/1/101/set')->assertNotFound();
        $this->get('/persons/extract/101')->assertNotFound();
    }

    #[Test]
    public function it_removes_the_legacy_authentication_and_registration_routes(): void
    {
        $this->get('/login')->assertNotFound();
        $this->get('/login/auth/token')->assertNotFound();
        $this->post('/sign-in')->assertNotFound();
        $this->get('/sign-out')->assertNotFound();
        $this->get('/registration')->assertNotFound();
        $this->post('/registration/data')->assertNotFound();
        $this->get('/500')->assertNotFound();
    }
}
