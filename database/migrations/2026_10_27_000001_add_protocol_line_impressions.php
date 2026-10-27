<?php

declare(strict_types=1);

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $connection = app(DatabaseManager::class)->connection($this->getConnection());
        $invalid = $connection->table('protocol_lines')
            ->leftJoin('distances', 'distances.id', '=', 'protocol_lines.distance_id')
            ->leftJoin('events', 'events.id', '=', 'distances.event_id')
            ->where(static function (Builder $query): void {
                $query->whereNull('distances.id')->orWhereNull('events.id')
                    ->orWhereNull('events.created_at')->orWhereNull('events.created_by')
                    ->orWhereNull('events.updated_at')->orWhereNull('events.updated_by');
            })
            ->value('protocol_lines.id');

        if ($invalid !== null) {
            throw new RuntimeException("Cannot backfill impressions for protocol line {$invalid}: missing distance, event or event impression.");
        }

        $schema = $connection->getSchemaBuilder();
        $schema->table('protocol_lines', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable();
        });

        $values = [];
        foreach (['created_at', 'created_by', 'updated_at', 'updated_by'] as $column) {
            $values[$column] = $connection->table('distances')
                ->join('events', 'events.id', '=', 'distances.event_id')
                ->whereColumn('distances.id', 'protocol_lines.distance_id')
                ->select("events.{$column}");
        }
        $connection->table('protocol_lines')->update($values);

        $schema->table('protocol_lines', static function (Blueprint $table): void {
            $table->dateTime('created_at')->nullable(false)->change();
            $table->integer('created_by')->nullable(false)->change();
            $table->dateTime('updated_at')->nullable(false)->change();
            $table->integer('updated_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        app(DatabaseManager::class)->connection($this->getConnection())
            ->getSchemaBuilder()->table('protocol_lines', static function (Blueprint $table): void {
                $table->dropColumn(['created_at', 'created_by', 'updated_at', 'updated_by']);
            });
    }
};
