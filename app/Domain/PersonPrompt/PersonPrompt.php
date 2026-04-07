<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Auth\Impression;
use App\Domain\Cup\Event\CupCreated;
use App\Domain\Cup\Event\CupUpdated;
use App\Domain\Cup\Factory\CupInput;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\Event\PersonPromptCreated;
use App\Domain\PersonPrompt\Event\PersonPromptUpdated;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $person_id
 * @property string $prompt
 * @property string $metaphone
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Person $person
 */
class PersonPrompt extends AggregatedModel
{
    use HasFactory;

    protected $table = 'persons_prompt';
    protected $fillable = ['prompt'];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }

    public function create(): void
    {
        $this->recordThat(new PersonPromptCreated($this));

        $this->save();
    }

    public function updateData(string $prompt, string $metaphone, Impression $impression): void
    {
        $this->prompt = $prompt;
        $this->metaphone = $metaphone;
        $this->updated = $impression;

        $this->recordThat(new PersonPromptUpdated($this));
    }

    protected function casts(): array
    {
        return [
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
