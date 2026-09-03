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
}
