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
        Schema::table('groups', static function (Blueprint $table): void {
            $table->string('normalize_name')->nullable()->after('name');
            $table->boolean('active')->default(true)->after('normalize_name');
            $table->timestamp('created_at')->useCurrent()->after('active');
            $table->unsignedBigInteger('created_by')->default(10)->after('created_at');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->after('created_by');
            $table->unsignedBigInteger('updated_by')->default(10)->after('updated_at');
        });

        DB::table('groups')->orderBy('id')->each(static function (object $group): void {
            DB::table('groups')->where('id', $group->id)->update([
                'normalize_name' => mb_strtolower(trim($group->name)),
            ]);
        });

        Schema::table('groups', static function (Blueprint $table): void {
            $table->string('normalize_name')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('groups', static function (Blueprint $table): void {
            $table->dropColumn(['normalize_name', 'active', 'created_at', 'created_by', 'updated_at', 'updated_by']);
        });
    }
};
