<?php

use App\Models\Person;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPersonForeingKeyOnProtocolLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $personsIds = Person::all('id');

        DB::table('protocol_lines')
            ->whereNotIn('person_id', $personsIds)
            ->update(['person_id' => null]);

        DB::table('persons_prompt')
            ->whereNotIn('person_id', $personsIds)
            ->delete();

        Schema::table('protocol_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable(true)->change();
            $table->foreign('person_id', 'fk_protocol_person_id')
                ->references('id')->on('person')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('protocol_lines', function (Blueprint $table) {
            $table->dropForeign('fk_protocol_person_id');
        });

        Schema::table('protocol_lines', function (Blueprint $table) {
            $table->integer('person_id')->nullable(true)->change();
        });
    }
}
