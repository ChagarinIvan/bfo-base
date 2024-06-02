<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Service\Person\UpdatePersonInfo;
use App\Domain\Person\Person;
use App\Domain\User\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class UpdatePersonActionTest extends TestCase
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
     * @test
     * @see UpdatePersonAction::class
     */
    public function it_updates_person_info(): void
    {
        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        Person::factory(state: ['id' => 1, 'club_id' => 2, 'birthday' => '1989-01-01', 'citizenship' => 'belarus'])->createOne();

        $this->post('/persons/1/update', [
            'firstname' => 'test name',
            'lastname' => 'test lastname',
            'citizenship' => 'other',
            'clubId' => '3',
            'birthday' => '1988-01-01',
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/persons/1/show')
        ;

        $this->assertDatabaseHas('person', [
            'id' => 1,
            'firstname' => 'test name',
            'citizenship' => 'other',
            'club_id' => 3,
            'birthday' => '1988-01-01',
            'updated_by' => $user->id,
        ]);
    }
}
