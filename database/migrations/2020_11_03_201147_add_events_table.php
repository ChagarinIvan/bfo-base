<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('events', static function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('type')->nullable(true)->default('');
            $table->string('description')->nullable(true)->default('');
            $table->date('date')->nullable(false);
            $table->bigInteger('competition_id')->unsigned()->nullable(false);
            $table->timestamps();

            $table->foreign('competition_id')->references('id')->on('competitions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
}
