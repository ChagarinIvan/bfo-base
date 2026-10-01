<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('protocol_ident_queue');
    }

    public function down(): void
    {
        Schema::create('protocol_ident_queue', static function (Blueprint $table): void {
            $table->id();
            $table->string('ident_line')->unique()->default('')->index();
        });
    }
};
