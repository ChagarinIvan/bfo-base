<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Transform\Rector\String_\StringToClassConstantRector;
use RectorLaravel\Rector\MethodCall\AssertSeeToAssertSeeHtmlRector;
use RectorLaravel\Set\LaravelLevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/tests',
    ])
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_130,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    // Современные конструкции PHP: версия берётся из composer floor (^8.5) —
    // набор «едет» вместе со стеком, версию не хардкодим.
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        if: true,
        earlyReturn: true,
    )
    ->withComposerBased(
        phpunit: true,
        laravel: true,
    )
    // Импорт вместо FQCN (см. CLAUDE.md / конституция): атрибуты и классы,
    // которые rector генерирует как \Fully\Qualified, подключаем через use.
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withSkip([
        // генерируемые Laravel файлы кэша — не линтим (как в .php-cs-fixer.php)
        __DIR__ . '/bootstrap/cache',
        AssertSeeToAssertSeeHtmlRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        StringToClassConstantRector::class,
        // `assertNull()` is the precise assertion for nullable values; keeping it
        // avoids Rector rewriting it to the less explicit `assertNotInstanceOf()`.
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        // PHP 8.5 hooked properties cannot be declared readonly on the containing class.
        ReadOnlyPropertyRector::class => [
            __DIR__ . '/app/Application/Service/Competition/ListCompetitions.php',
        ],
        RenamePropertyRector::class,
        // Blade::component() регистрирует классовые компоненты; авто-переименование в
        // aliasComponent() (правило из laravel70) ломает bootstrap — регистрируем как есть.
        RenameMethodRector::class => [
            __DIR__ . '/app/Bridge/Laravel/Provider/ViewProvider.php',
        ],
    ]);
