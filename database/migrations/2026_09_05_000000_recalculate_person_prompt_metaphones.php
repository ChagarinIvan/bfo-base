<?php

declare(strict_types=1);

use App\Domain\PersonPrompt\TranslitPersonPromptMetaphone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Mav\Slovo\Phonetics;

return new class extends Migration
{
    public function up(): void
    {
        $metaphone = new TranslitPersonPromptMetaphone(new Phonetics());

        DB::table('persons_prompt')->orderBy('id')->each(static function (object $prompt) use ($metaphone): void {
            DB::table('persons_prompt')->where('id', $prompt->id)->update([
                'metaphone' => $metaphone->calculate($prompt->prompt),
            ]);
        });
    }

    public function down(): void
    {
    }
};
