<?php

namespace Tests\Domain\Hasher;

use App\Domain\Hasher\StandardHasher;
use Tests\TestCase;

class StandardHasherTest extends TestCase
{
    protected function setUp(): void
    {
        $this->hasher = new StandardHasher();
    }

    /**
     * @test
     * @dataProvider hash_test_data
     */
    public function it_hashes_string(mixed $value, string $expectedHash): void
    {
        $actualHash = $this->hasher->hash($value);
        $this->assertEquals($expectedHash, $actualHash);
    }

    public function hash_test_data(): array
    {
        return [
            [
                'test',
                '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'
            ],
            [
                'test1',
                '1b4f0e9851971998e732078544c96b36c3d01cedf7caa332359d6f1d83567014'
            ],
            [
                ['test1', 'test2'],
                '364c9a3b2164461f81eb7d0b5ced150b8f5a41b9eea0b07773306c4bf9d906a8',
            ],
            [
                [1 => 'test1', 'test' =>'test2'],
                '364c9a3b2164461f81eb7d0b5ced150b8f5a41b9eea0b07773306c4bf9d906a8',
            ],
            [
                ['test2', 'test' =>'test1'],
                '364c9a3b2164461f81eb7d0b5ced150b8f5a41b9eea0b07773306c4bf9d906a8',
            ],
            [
                ['test1', ['test2']],
                'a5ebc1ad84c5652f5de341c07d19da5f22a4a9efbfeeff811834ac82ebc2f4db',
            ],
            [
                null,
                'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            ],
            [
                1,
                '6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',
            ],
            [
                123,
                'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3',
            ],
            [
                123.001,
                'c5cbda73573f31de559e13aecd20c473a13d51cfde066f7dfa1ea21227599853',
            ],
            [
                true,
                '6b86b273ff34fce19d6b804eff5a3f5747ada4eaa22f1d49c01e52ddb7875b4b',
            ],
            [
                false,
                'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
            ],
            [
                new Foo('test', ['test1', 'test2'], new Baz('test', ['test'])),
                '2df3802806a3c1f8d04e675193eb346eac8979fba7a19275eb331a17b66dd908',
            ],
            [
                new Foo('test', ['test2', 'test1'], new Baz('test', [])),
                '4b47c2ff1bcfd4225d0a9ca57faecc9e724cecab790bf5d31d223ccbe81815b7',
            ],
            [
                new Foo('', [], new Baz('', [])),
                '50fa56653bb5250f718bff44b200501bbddcd429c72dc3f27d6ad36af5274d39',
            ],
            [
                fn() => new Foo('', [], new Baz('', [])),
                '50fa56653bb5250f718bff44b200501bbddcd429c72dc3f27d6ad36af5274d39',
            ],
            [
                fn() => 'test',
                '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
            ],
        ];
    }
}

final class Foo {
    public function __construct(
        private readonly string $a,
        protected readonly array $b,
        public readonly Baz $c,
    ) {}
}

final class Baz {
    public function __construct(
        private readonly string $c,
        protected readonly array $d,
    ) {}
}
