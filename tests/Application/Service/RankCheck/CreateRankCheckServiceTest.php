<?php

declare(strict_types=1);

namespace Tests\Application\Service\RankCheck;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\RankCheck\RankCheckAssembler;
use App\Application\Dto\RankCheck\RankCheckListDto;
use App\Application\Service\RankCheck\CreateRankCheck;
use App\Application\Service\RankCheck\CreateRankCheckService;
use App\Application\Service\RankCheck\Exception\InvalidRankCheckList;
use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Exception\UnableToCreate;
use App\Domain\RankCheck\Factory\RankCheckFactory;
use App\Domain\RankCheck\Factory\RankCheckInput;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\RankCheck\RankCheckStatus;
use App\Domain\Shared\Storage;
use App\Domain\Shared\UuidGenerator;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

final class CreateRankCheckServiceTest extends TestCase
{
    private CreateRankCheckService $service;

    private MockObject&RankCheckFactory $factory;

    private MockObject&RankCheckRepository $checks;

    private MockObject&Storage $storage;

    private MockObject&UuidGenerator $uuidGenerator;

    private static function matchesRankCheckInput(RankCheckInput $input): bool
    {
        return $input->userId === 7
            && $input->sourcePath === 'rank-checks/check-uuid.csv'
            && $input->content === 'csv-content'
            && $input->extension === 'csv';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CreateRankCheckService(
            checks: $this->checks = $this->createMock(RankCheckRepository::class),
            storage: $this->storage = $this->createMock(Storage::class),
            uuidGenerator: $this->uuidGenerator = $this->createMock(UuidGenerator::class),
            factory: $this->factory = $this->createMock(RankCheckFactory::class),
            assembler: new RankCheckAssembler(new AuthAssembler()),
        );
    }

    #[Test]
    public function it_creates_and_stores_rank_check(): void
    {
        $this->uuidGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('check-uuid')
        ;

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(static fn(RankCheckInput $input): bool => self::matchesRankCheckInput($input)))
            ->willReturn($check = $this->rankCheckStub())
        ;

        $this->storage
            ->expects($this->once())
            ->method('put')
            ->with('rank-checks/check-uuid.csv', 'csv-content')
        ;

        $this->checks
            ->expects($this->once())
            ->method('add')
            ->with($check)
        ;

        $result = $this->service->execute($this->command('csv-content', 'csv'));

        $this->assertSame('17', $result->id);
        $this->assertSame(RankCheckStatus::Parsing->value, $result->status);
    }

    #[Test]
    public function it_maps_domain_creation_error_to_invalid_list(): void
    {
        $this->uuidGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('check-uuid')
        ;

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new UnableToCreate())
        ;

        $this->storage->expects($this->never())->method('put');
        $this->checks->expects($this->never())->method('add');
        $this->expectException(InvalidRankCheckList::class);

        $this->service->execute($this->command('', 'txt'));
    }

    private function command(string $content, string $extension): CreateRankCheck
    {
        $file = $this->createStub(UploadedFile::class);
        $file->method('getContent')->willReturn($content);
        $file->method('getClientOriginalExtension')->willReturn($extension);

        $list = new RankCheckListDto();
        $list->list = $file;

        return new CreateRankCheck($list, new UserId(7));
    }

    private function rankCheckStub(): RankCheck
    {
        $impression = new Impression(new Carbon('2026-09-17 12:00:00'), 7);
        $check = $this->createStub(RankCheck::class);
        $check->method('__get')->willReturnMap([
            ['id', 17],
            ['status', RankCheckStatus::Parsing],
            ['created', $impression],
            ['updated', $impression],
            ['error_message', null],
        ]);

        return $check;
    }
}
