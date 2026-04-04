<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Club;

use App\Bridge\Laravel\Http\Controllers\Club\ShowClubAction;
use App\Domain\Club\Club;
use App\Domain\Person\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class ShowClubActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * @see ShowClubAction::class
     */
    #[Test]
    public function it_shows_clubs(): void
    {
        /** @var Club $club */
        $club = Club::factory(state: ['name' => 'test1'])->createOne();
        Club::factory(state: ['name' => 'test2'])->createOne();
        Person::factory(state: ['firstname' => 'test1', 'club_id' => $club->id])->create();
        Person::factory(state: ['firstname' => 'test2', 'club_id' => $club->id])->create();

        $this->get("/clubs/$club->id/show")
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeTextInOrder([
                'test1',
                'test2',
            ])
        ;
    }
}
