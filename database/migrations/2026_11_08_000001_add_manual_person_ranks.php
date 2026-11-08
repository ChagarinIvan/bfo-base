<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $schema = app(DatabaseManager::class)->connection()->getSchemaBuilder();
        $schema->create('manual_person_ranks', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('person_id')->constrained('person')->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->date('achieved_on');
            $table->date('activated_on');
            $table->boolean('active');
            $table->dateTime('created_at');
            $table->unsignedBigInteger('created_by');
            $table->dateTime('updated_at');
            $table->unsignedBigInteger('updated_by');
            $table->index(['person_id', 'active']);
        });
        $schema->table('person_rank_histories', static function (Blueprint $table): void {
            foreach (['protocol_line_id', 'distance_id', 'event_id', 'competition_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable()->change();
            }
            $table->foreignUuid('manual_rank_id')->nullable()->constrained('manual_person_ranks');
        });
    }

    public function down(): void
    {
        $connection = app(DatabaseManager::class)->connection();
        $schema = $connection->getSchemaBuilder();
        $connection->table('person_rank_histories')->whereNotNull('manual_rank_id')->delete();
        $schema->table('person_rank_histories', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('manual_rank_id');
            foreach (['protocol_line_id', 'distance_id', 'event_id', 'competition_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable(false)->change();
            }
        });
        $schema->drop('manual_person_ranks');
    }
};
