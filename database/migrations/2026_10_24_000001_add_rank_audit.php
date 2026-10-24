<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_rank_audit', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('person_id');
            $table->string('trigger', 32);
            $table->timestamp('created_at');
            $table->unsignedInteger('created_by');
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->index(['person_id', 'id']);
        });

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->timestamps();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
        });

        DB::table('protocol_lines')
            ->join('distances', 'protocol_lines.distance_id', '=', 'distances.id')
            ->join('events', 'distances.event_id', '=', 'events.id')
            ->update([
                'protocol_lines.created_at' => DB::raw('events.created_at'),
                'protocol_lines.created_by' => DB::raw('events.created_by'),
                'protocol_lines.updated_at' => DB::raw('events.updated_at'),
                'protocol_lines.updated_by' => DB::raw('events.updated_by'),
            ]);

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->timestamp('created_at')->nullable(false)->change();
            $table->unsignedInteger('created_by')->nullable(false)->change();
            $table->timestamp('updated_at')->nullable(false)->change();
            $table->unsignedInteger('updated_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropColumn(['created_at', 'created_by', 'updated_at', 'updated_by']);
        });
        Schema::dropIfExists('person_rank_audit');
    }
};
