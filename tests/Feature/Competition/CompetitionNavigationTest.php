<?php

declare(strict_types=1);

namespace Tests\Feature\Competition;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class CompetitionNavigationTest extends TestCase
{
    #[Test]
    public function root_redirects_to_the_competitions_spa(): void
    {
        $this->get('/')->assertRedirect('/app/competitions');
    }

    #[Test]
    public function legacy_competition_pages_are_not_registered(): void
    {
        $this->get('/competitions')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get('/competitions/1/show')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get('/competitions/create')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get('/competitions/1/edit')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->post('/competitions/store')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->post('/competitions/1/update')->assertStatus(Response::HTTP_NOT_FOUND);
        $this->get('/competitions/1/delete')->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
