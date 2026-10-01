<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Event\Event\EventIdentified;
use App\Domain\Event\Event\EventInfoUpdated;
use App\Domain\Event\Event\EventParsed;
use App\Domain\Event\Event\EventParsingStarted;
use App\Domain\Event\Event\EventProcessingFailed;
use App\Domain\Event\Event\EventRanksUpdated;
use App\Domain\Event\Exception\EventParsingError;
use App\Domain\Person\EventPersonRankUpdater;
use App\Domain\ProtocolLine\Exception\UnableToCreateProtocolLine;
use App\Domain\ProtocolLine\Factory\ProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineIdentifier;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Database\Factories\Domain\Event\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use LogicException;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $date
 * @property int $competition_id
 * @property string $file
 * @property bool $active
 * @property EventProcessingStatus $processing_status
 * @property string $processing_token
 * @property string|null $error_message
 * @property-read int $protocol_lines_count
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read Competition|null $competition
 * @property-read Collection|ProtocolLine[] $protocolLines
 * @property-read Collection|Distance[] $distances
 * @property-read Collection|CupEvent[] $cups
 */
#[Fillable([
    'name', 'description', 'date'
])]
#[Table(name: 'events')]
class Event extends AggregatedModel
{
    /** @see EventFactory */
    use HasFactory;

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'id', 'competition_id');
    }

    public function protocolLines(): HasManyThrough
    {
        return $this->hasManyThrough(ProtocolLine::class, Distance::class, 'event_id', 'distance_id', 'id', 'id');
    }

    public function distances(): HasMany
    {
        return $this->hasMany(Distance::class, 'event_id', 'id');
    }

    public function cups(): HasMany
    {
        return $this->hasMany(CupEvent::class);
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new EventDisabled($this));
    }

    public function updateInfo(UpdateInput $input, Impression $impression): void
    {
        $this->name = $input->info->name;
        $this->description = $input->info->description;
        $this->date = $input->info->date;
        $this->updated = $impression;

        $this->recordThat(new EventInfoUpdated($this));
    }

    public function updateProtocol(ProtocolUpdater $updater, Protocol $protocol, Impression $impression): void
    {
        $this->file = $updater->update($this, $protocol, $impression);
        $this->updated = $impression;
    }

    public function create(): void
    {
        $this->recordThat(new EventCreated($this));

        if ($this->processing_status === EventProcessingStatus::PARSING) {
            $this->recordThat(new EventParsingStarted($this->id, $this->processing_token, $this->created));
        }

        $this->save();
    }

    public function startProtocolProcessing(string $token, Impression $impression): void
    {
        $state = $this->processingState()->start($token);
        $this->applyProcessingState($state, $impression);

        $this->recordThat(new EventParsingStarted($this->id, $token, $impression));
    }

    public function completeParsing(string $token, Impression $impression): bool
    {
        return $this->advanceProcessing($token, EventProcessingStatus::PARSING, $impression);
    }

    /** @return list<ProtocolLine> */
    public function parse(
        string $token,
        ProtocolParser $parser,
        ProtocolLinesFactory $linesFactory,
        Impression $impression,
    ): array {
        if ($this->processing_token !== $token || $this->processing_status !== EventProcessingStatus::PARSING) {
            return [];
        }

        try {
            $protocolLinesInputs = $parser->parse($this);
            $lines = $linesFactory->create($this, $protocolLinesInputs);
            $this->processing_status = EventProcessingStatus::IDENTIFYING;

            // когда протокол лайны будут иметь Impression, то надо брать с последнего протокол лайна ->created
            $this->recordThat(new EventParsed($this->id, $token, $impression));
        } catch (EventParsingError|UnableToCreateProtocolLine $e) {
            $this->processing_status = EventProcessingStatus::PARSING_ERROR;
            $this->error_message = $e->getMessage();
            $lines = [];

            $this->recordThat(new EventProcessingFailed($this->id, $token, $this->processing_status, $impression));
        } finally {
            $this->updated = $impression;
        }

        return $lines;
    }

    public function ident(
        string $token,
        ProtocolLineIdentifier $identifier,
        Impression $impression,
    ): void {
        if ($this->processing_token !== $token || $this->processing_status !== EventProcessingStatus::IDENTIFYING) {
            return;
        }

        try {
            $identifier->identify($this, $impression);
            $this->processing_status = EventProcessingStatus::REBUILDING_RANKS;

            $this->recordThat(new EventIdentified($this->id, $token, $impression));
        } catch (EventParsingError|UnableToCreateProtocolLine $e) {
            $this->processing_status = EventProcessingStatus::IDENTIFYING_ERROR;
            $this->error_message = $e->getMessage();

            $this->recordThat(new EventProcessingFailed($this->id, $token, $this->processing_status, $impression));
        } finally {
            $this->updated = $impression;
        }
    }

    public function completeRankRebuild(string $token, Impression $impression): bool
    {
        return $this->advanceProcessing($token, EventProcessingStatus::REBUILDING_RANKS, $impression);
    }

    public function updateRanks(
        string $token,
        EventPersonRankUpdater $updater,
        EventProcessingLease $lease,
        Impression $impression,
    ): bool {
        if ($this->processing_token !== $token || $this->processing_status !== EventProcessingStatus::REBUILDING_RANKS) {
            return false;
        }

        $updater->update($this, $token, $lease, $impression);
        $lease->assertOwned();

        return $this->completeRankRebuild($token, $impression);
    }

    public function failProcessing(string $token, string $safeMessage, Impression $impression): bool
    {
        $state = $this->processingState()->fail($token, $safeMessage);

        if ($state === null) {
            return false;
        }

        $this->applyProcessingState($state, $impression);
        $this->recordThat(new EventProcessingFailed($this->id, $token, $state->status, $impression));

        return true;
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d',
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
            'processing_status' => EventProcessingStatus::class,
        ];
    }

    private function advanceProcessing(string $token, EventProcessingStatus $expected, Impression $impression): bool
    {
        $state = $this->processingState()->advance($token, $expected);

        if ($state === null) {
            return false;
        }

        $this->applyProcessingState($state, $impression);

        $event = match ($expected) {
            EventProcessingStatus::PARSING => new EventParsed($this->id, $token, $impression),
            EventProcessingStatus::IDENTIFYING => new EventIdentified($this->id, $token, $impression),
            EventProcessingStatus::REBUILDING_RANKS => new EventRanksUpdated($this->id, $token, $impression),
            default => throw new LogicException('Invalid processing stage.'),
        };
        $this->recordThat($event);

        return true;
    }

    private function processingState(): EventProcessingState
    {
        return new EventProcessingState($this->processing_status, $this->processing_token, $this->error_message);
    }

    private function applyProcessingState(EventProcessingState $state, Impression $impression): void
    {
        $this->processing_status = $state->status;
        $this->processing_token = $state->token;
        $this->error_message = $state->errorMessage;
        $this->updated = $impression;
    }
}
