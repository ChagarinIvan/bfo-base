<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_protocols', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->uuid('run_token')->unique();
            $table->string('status', 32);
            $table->unsignedInteger('total_lines')->default(0);
            $table->unsignedInteger('identified_lines')->default(0);
            $table->json('identified_line_ids')->nullable();
            $table->uuid('rank_batch_id')->nullable()->unique();
            $table->unsignedInteger('rank_jobs_total')->default(0);
            $table->unsignedInteger('rank_jobs_completed')->default(0);
            $table->timestamps();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->index(['event_id', 'status']);
        });

        Schema::table('events', static function (Blueprint $table): void {
            $table->foreignId('active_event_protocol_id')->nullable()->after('file')->constrained('event_protocols')->nullOnDelete();
        });

        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->foreignId('event_protocol_id')->nullable()->after('person_id')->constrained()->nullOnDelete();
            $table->index(['event_protocol_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::table('protocol_lines', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_protocol_id');
        });
        Schema::table('events', static function (Blueprint $table): void {
            $table->dropConstrainedForeignId('active_event_protocol_id');
        });
        Schema::dropIfExists('event_protocols');
    }
};
