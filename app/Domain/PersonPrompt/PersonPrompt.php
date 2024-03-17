<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
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
class PersonPrompt extends Model
{
    use HasFactory;

    protected $table = 'persons_prompt';
    protected $fillable = ['prompt'];

    protected $casts = [
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }
}
