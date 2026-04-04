<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
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
        LaravelLevelSetList::UP_TO_LARAVEL_110,
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withComposerBased(
        phpunit: true,
        laravel: true,
    )
    ->withSkip([
        ExplicitBoolCompareRector::class,
        AssertSeeToAssertSeeHtmlRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        StringToClassConstantRector::class,
        RenamePropertyRector::class,
    ])
    ;
