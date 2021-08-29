<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonForeingKeyInPersonPromptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('persons_prompt', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->change();
            $table->foreign('person_id', 'fk_person_prompt_person_id')
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
        Schema::table('persons_prompt', function (Blueprint $table) {
            $table->dropForeign('fk_person_prompt_person_id');
        });

        Schema::table('persons_prompt', function (Blueprint $table) {
            $table->integer('person_id')->change();
        });
    }
}
