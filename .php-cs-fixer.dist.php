<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
;

// Style-only ruleset: the risky fixers (@Symfony:risky, declare_strict_types, strict_param,
// strict_comparison) are intentionally left out — large parts of the bundle have no test
// coverage yet, and those fixers can change runtime behavior.
return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => true,
        'single_line_throw' => false,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
    ])
    ->setFinder($finder)
;
