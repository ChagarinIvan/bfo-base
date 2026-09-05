<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\PersonPrompt\Event\PersonPromptCreated;
use App\Domain\PersonPrompt\Event\PersonPromptDisabled;
use App\Domain\PersonPrompt\Event\PersonPromptUpdated;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $person_id
 * @property string $prompt
 * @property string $metaphone
 * @property bool $active
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Person $person
 */
#[Fillable(['prompt', 'active'])]
#[Table(name: 'persons_prompt')]
class PersonPrompt extends AggregatedModel
{
    use HasFactory;

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }

    public function create(): void
    {
        $this->recordThat(new PersonPromptCreated($this));

        $this->save();
    }

    public function updateData(string $prompt, string $metaphone, Impression $impression, ?int $personId = null): void
    {
        $this->prompt = $prompt;
        $this->metaphone = $metaphone;
        $this->person_id = $personId ?? $this->person_id;
        $this->updated = $impression;

        $this->recordThat(new PersonPromptUpdated($this));
    }

    public function disable(Impression $impression): void
    {
        $this->active = false;
        $this->updated = $impression;

        $this->recordThat(new PersonPromptDisabled($this));
    }

    protected function casts(): array
    {
        return [
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
