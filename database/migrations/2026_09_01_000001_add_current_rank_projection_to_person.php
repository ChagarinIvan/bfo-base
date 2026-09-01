<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->string('current_rank')->default('without_rank')->after('active');
            $table->date('current_rank_started_on')->nullable()->after('current_rank');
            $table->date('current_rank_activated_on')->nullable()->after('current_rank_started_on');
            $table->date('current_rank_finished_on')->nullable()->after('current_rank_activated_on');
            $table->index(['current_rank', 'active'], 'person_current_rank_active_index');
            $table->index('current_rank_finished_on', 'person_current_rank_finished_index');
        });
    }

    public function down(): void
    {
        Schema::table('person', static function (Blueprint $table): void {
            $table->dropIndex('person_current_rank_active_index');
            $table->dropIndex('person_current_rank_finished_index');
            $table->dropColumn([
                'current_rank',
                'current_rank_started_on',
                'current_rank_activated_on',
                'current_rank_finished_on',
            ]);
        });
    }
};
