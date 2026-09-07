<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('person_rank_histories')
            ->where('change_type', 'downgrade')
            ->update(['change_type' => 'lower_qualification']);
    }

    public function down(): void
    {
        DB::table('person_rank_histories')
            ->where('change_type', 'lower_qualification')
            ->update(['change_type' => 'downgrade']);
    }
};
