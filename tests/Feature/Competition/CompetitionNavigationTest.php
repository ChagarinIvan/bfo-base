<?php

declare(strict_types=1);

namespace Tests\Feature\Competition;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use function file_get_contents;

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

    #[Test]
    public function legacy_competition_presentation_files_are_removed(): void
    {
        $this->assertDirectoryDoesNotExist(base_path('app/Bridge/Laravel/Http/Controllers/Competition'));
        $this->assertDirectoryDoesNotExist(resource_path('views/competitions'));
        $this->assertDirectoryDoesNotExist(base_path('tests/Bridge/Laravel/Http/Controllers/Competition'));
        $this->assertStringNotContainsString('ShowCompetitionAction', (string) file_get_contents(resource_path('views/events/show.blade.php')));
    }
}
