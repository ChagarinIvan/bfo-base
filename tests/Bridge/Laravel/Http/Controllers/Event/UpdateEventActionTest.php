<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Event;

use App\Bridge\Laravel\Http\Controllers\Event\UpdateEventAction;
use App\Domain\User\User;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

final class UpdateEventActionTest extends TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Storage::delete('2023/2023-01-01_test_event.text/html');
    }

    /**
     * @test
     * @see UpdateEventAction::class
     */
    public function it_updates_event_without_file(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $this->post('events/101/update', [
            'name' => 'test event',
            'description' => 'test event description',
            'date' => '2023-01-01'
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/events/101')
        ;

        $this->assertDatabaseHas('events', [
            'name' => 'test event',
            'description' => 'test event description',
            'date' => '2023-01-01',
            'active' => true,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseCount('distances', 4);
        $this->assertDatabaseCount('protocol_lines', 6);
    }

    /**
     * @test
     * @see UpdateEventAction::class
     */
    public function it_updates_event_with_protocol(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Authenticatable&User $user */
        $user = User::factory()->createOne();
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('test.html', $this->content());

        $this->post('events/101/update', [
            'name' => 'test event',
            'description' => 'test event description',
            'date' => '2023-01-01',
            'protocol' => $file,
        ])
            ->assertStatus(Response::HTTP_FOUND)
            ->assertRedirect('/events/101')
        ;

        Storage::delete('2023/2023-01-01_test_event.text/html');

        $this->assertDatabaseCount('distances', 3);
        $this->assertDatabaseCount('protocol_lines', 3);
    }

    private function content(): string
    {
        return <<<HTML
<div id="results-body">
    <h1>Чемпионат и Первенство Могилевской области по лыжному ориентированию 2024 Спринт<br>
        27.01.2024, Печерский лесопарк<br>
        <br>ПРОТОКОЛ РЕЗУЛЬТАТОВ
    </h1>
    <h2><span id="m0"></span>М21A, 23 КП, 1.100 м</h2>
    <pre>-------------------------------------------------------------------------------------------------
№п/п Фамилия, имя              Коллектив            Квал Номер ГР  Результат Место Вып  Oчки Прим
-------------------------------------------------------------------------------------------------
   1 Зайцев Игорь              КО «Ультра»          II     39 1973 00:16:26      1 I     100
-------------------------------------------------------------------------------------------------
Класс дистанции    - I
Ранг соревнований  - 71,2 баллов
I     -  105%  -  0:17:16
II    -  123%  -  0:20:13
III   -  146%  -  0:24:00
</pre>
    <h2><span id="m1"></span>М21E, 23 КП, 1.100 м</h2>
    <pre>-------------------------------------------------------------------------------------------------
№п/п Фамилия, имя              Коллектив            Квал Номер ГР  Результат Место Вып  Oчки Прим
-------------------------------------------------------------------------------------------------
   1 Языков Юрий               КСО «Немига-Норд»    КМС    92 1990 00:12:03      1 КМС   100
-------------------------------------------------------------------------------------------------
Класс дистанции    - КМС
Ранг соревнований  - 360,0 баллов
КМС   -  111%  -  0:13:23
I     -  126%  -  0:15:11
II    -  146%  -  0:17:36
III   -  174%  -  0:20:59
</pre>
<pre>Главный судья                                   Черный П.Л.
Главный секретарь                               Каржова М.В.</pre>

</div>
HTML;
    }
}
