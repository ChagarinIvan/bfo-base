<?php

declare(strict_types=1);

use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Shared\SymbolNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $normalizer = new GroupNameNormalizer(new SymbolNormalizer());

        DB::table('groups')->orderBy('id')->each(static function (object $group) use ($normalizer): void {
            DB::table('groups')->where('id', $group->id)->update([
                'normalize_name' => $normalizer->normalize($group->name),
            ]);
        });
    }

    public function down(): void
    {
    }
};
