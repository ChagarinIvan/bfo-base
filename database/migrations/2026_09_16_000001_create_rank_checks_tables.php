<?php

declare(strict_types=1);

use App\Domain\Auth\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rank_checks', static function (Blueprint $table): void {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->default(User::SYSTEM_USER_ID);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('updated_by')->default(User::SYSTEM_USER_ID);
            $table->string('status', 16)->index();
            $table->string('source_path');
            $table->text('error_message')->nullable();
        });

        Schema::create('rank_check_rows', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rank_check_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('group')->nullable();
            $table->string('name');
            $table->string('club')->nullable();
            $table->string('rank')->nullable();
            $table->string('number')->nullable();
            $table->string('year')->nullable();
            $table->foreignId('person_id')->nullable()->constrained('person')->nullOnDelete();
            $table->string('database_name')->nullable();
            $table->string('database_club')->nullable();
            $table->string('database_rank')->nullable();
            $table->string('database_year')->nullable();
            $table->boolean('has_person')->default(false);
            $table->boolean('is_equal')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->default(User::SYSTEM_USER_ID);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedBigInteger('updated_by')->default(User::SYSTEM_USER_ID);
            $table->unique(['rank_check_id', 'position']);
            $table->index(['rank_check_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rank_check_rows');
        Schema::dropIfExists('rank_checks');
    }
};
