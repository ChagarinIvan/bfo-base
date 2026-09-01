<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Http\Controllers\Event;

use App\Domain\Club\Club;
use App\Domain\ProtocolLine\ProtocolLine;
use Database\Seeders\ProtocolLinesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ShowEventActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_links_a_club_for_a_protocol_line_with_outer_whitespace(): void
    {
        $this->seed(ProtocolLinesSeeder::class);

        /** @var Club $club */
        $club = Club::factory()->createOne([
            'name' => 'Тэст клуб',
            'normalize_name' => 'тэст клуб',
        ]);
        ProtocolLine::query()->findOrFail(101)->update(['club' => '  Тэст клуб  ']);

        $this->get('/events/101')
            ->assertOk()
            ->assertSee("/app/clubs/{$club->id}", false);
    }
}
