<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRules([
    'linebreak_after_opening_tag' => true,
    'method_argument_space' => [
        'keep_multiple_spaces_after_comma' => false,
    ],
    'static_lambda' => true,
    'global_namespace_import' => [
        'import_classes' => true,
        'import_functions' => true,
        'import_constants' => true,
    ],
    'self_static_accessor' => true,
    'no_useless_sprintf' => true,

    // @PhpCsFixer
    'align_multiline_comment' => [
        'comment_type' => 'phpdocs_like',
    ],
    'fully_qualified_strict_types' => true,
    'ordered_class_elements' => [
        'sort_algorithm' => 'none',
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public_static',
            'property_public',
            'property_protected_static',
            'property_protected',
            'property_private_static',
            'property_private',
            'method_public_static',
            'method_protected_static',
            'method_private_static',
            'method_public_abstract_static',
            'method_protected_abstract_static',
            'method_public_abstract',
            'method_protected_abstract',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
            'destruct',
        ],
    ],

    'no_extra_blank_lines' => [
        // Removed 'throw'.
        'tokens' => [
            'attribute',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'extra',
            'parenthesis_brace_block',
            'square_brace_block',
            'switch',
            'use',
        ],
    ],

    // @PhpCsFixer:risky
    'logical_operators' => true,

    // @PHP71Migration
    'ternary_to_null_coalescing' => true,

    // @PHP71Migration:risky
    'void_return' => true,
    'random_api_migration' => true,
    'pow_to_exponentiation' => true,
    'declare_strict_types' => true,

    // @Symfony overrides
    'single_line_throw' => false,
    'ordered_imports' => [
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
    ],
    'blank_line_between_import_groups' => false,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'yoda_style' => false,

    // @Symfony:risky
    'fopen_flags' => true,
    'native_function_invocation' => [
        'include' => [
            '@internal',
        ],
        'scope' => 'namespaced',
    ],
    'is_null' => true,
    'modernize_types_casting' => true,
    'dir_constant' => true,
    'non_printable_character' => [
        'use_escape_sequences_in_strings' => false,
    ],
    'self_accessor' => true,
    'no_alias_functions' => true,
    'function_to_constant' => true,
    'ereg_to_preg' => true,
    'fopen_flag_order' => true,
    'implode_call' => true,
    'php_unit_construct' => true,

    // PHPUnit
    'php_unit_method_casing' => [
        'case' => 'snake_case',
    ],
    'php_unit_test_annotation' => [
        'style' => 'annotation',
    ],

    // PHPDoc
    'phpdoc_align' => [
        'align' => 'left',
    ],
    'phpdoc_order' => true,
    'phpdoc_add_missing_param_annotation' => true,
    'phpdoc_var_annotation_correct_order' => true,
    'phpdoc_trim_consecutive_blank_line_separation' => true,
    'phpdoc_separation' => false,
])
    ->setFinder($finder)
;
