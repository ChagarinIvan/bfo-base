<?php
declare(strict_types=1);

use App\Models\Distance;
use App\Models\ProtocolLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('distances', static function (Blueprint $table): void {
            $table->id();
            $table->bigInteger('event_id')->unsigned()->nullable(false)->index();
            $table->integer('group_id')->nullable(false)->index();
            $table->integer('length')->nullable(false)->default(0);
            $table->integer('points')->nullable(false)->default(0);

            $table->foreign('event_id', 'fk_distance_event_id')
                ->references('id')->on('events')
                ->onDelete('cascade');
        });

        $protocolLinesDistances = new Collection();
        $protocolLines = ProtocolLine::all();
        $protocolLines = $protocolLines->groupBy('event_id');
        foreach ($protocolLines as $eventId => $eventLine) {
            $groupLines = $eventLine->groupBy('group_id');
            foreach ($groupLines as $groupId => $lines) {
                $distance = new Distance();
                $distance->event_id = $eventId;
                $distance->group_id = $groupId;
                $distance->save();
                foreach ($lines as $line) {
                    $protocolLinesDistances[$line->id] = $distance->id;
                }
            }
        }

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropForeign('fk_event_id');
            $table->dropColumn('event_id');
            $table->dropColumn('group_id');
            $table->bigInteger('distance_id')->unsigned()->nullable(true)->index();

            $table->foreign('distance_id', 'fk_protocol_line_distance_id')
                ->references('id')->on('distances')
                ->onDelete('cascade');
        });

        foreach ($protocolLinesDistances as $protocolId => $distanceId) {
            /** @var ProtocolLine $protocolLine */
            $protocolLine = ProtocolLine::find($protocolId);
            $protocolLine->distance_id = $distanceId;
            $protocolLine->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropColumn('distance_id');
            $table->dropForeign('fk_protocol_line_distance_id');

            $table->integer('group_id')->nullable(false)->index();
            $table->integer('event_id')->nullable(false)->index();
        });

        Schema::drop('distances');
    }
}
