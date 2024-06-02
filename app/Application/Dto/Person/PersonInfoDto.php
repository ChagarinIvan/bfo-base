<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use App\Domain\Person\Citizenship;
use Illuminate\Validation\Rules\Enum;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Mailer\Envelope;

final class PersonInfoDto extends AbstractDto
{
    public string $firstname;

    public string $lastname;

    public string $citizenship;

    public ?string $birthday = null;

    public ?string $clubId = null;

    public static function requestValidationRules(): array
    {
        return [
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'citizenship' => ['required', new Enum(Citizenship::class)],
            'birthday' => 'date',
            'clubId' => 'numeric',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->firstname = $data['firstname'];
        $this->lastname = $data['lastname'];
        $this->citizenship = $data['citizenship'];
        $this->setStringParam('birthday', $data);
        $this->clubId = isset($data['clubId']) ? (empty($data['clubId']) ? null : $data['clubId']) : null;

        return $this;
    }
}
