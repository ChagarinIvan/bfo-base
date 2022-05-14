<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetphoneField extends Migration
{
    public function up(): void
    {
        Schema::table('persons_prompt', function (Blueprint $table) {
            $table
                ->string('metaphone')
                ->nullable(false)
                ->default('')
                ->index(false)
            ;
        });
    }

    public function down(): void
    {
        Schema::table('persons_prompt', function (Blueprint $table) {
            $table->dropColumn('metaphone');
        });
    }
}
