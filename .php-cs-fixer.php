<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    // Usamos el set de reglas PER-CS 2.0, el estándar moderno sucesor de PSR-12.
    '@PER-CS2.0' => true,
    // Activamos reglas adicionales y personalizamos las del set base.
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => [
        'default' => 'single_space',
    ],
    'concat_space' => ['spacing' => 'one'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
    'unary_operator_spaces' => true,
    'class_attributes_separation' => [
        'elements' => [
            'const' => 'one',
            'method' => 'one',
            'property' => 'one',
        ],
    ],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => true,
    ],
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'allow_unused_params' => true],
    
    // Algunas reglas "risky" que son seguras y muy útiles para código moderno.
    'fully_qualified_strict_types' => true,
    'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
    'no_alias_functions' => ['sets' => ['@all']],
    'phpdoc_to_comment' => false, // Se desactiva para no perder PHPDocs valiosos.
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/lang',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);