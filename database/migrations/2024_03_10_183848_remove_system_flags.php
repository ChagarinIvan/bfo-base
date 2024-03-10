<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('system_flags');
    }

    public function down(): void
    {
        Schema::create('system_flags', static function (Blueprint $table): void {
            $table->id();
            $table->string('key')->nullable(false)->unique();
            $table->string('volume')->nullable(false);
        });
    }
};
