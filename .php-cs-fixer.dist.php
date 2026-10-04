<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()->in([__DIR__ . '/src', __DIR__ . '/examples', __DIR__ . '/tests']);

return new PhpCsFixer\Config()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x0Migration' => true,
        '@PHP8x0Migration:risky' => true,
        '@PHP8x1Migration' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x3Migration' => true,
        '@PHP8x4Migration' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => false,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'native_function_invocation' => ['include' => ['@internal'], 'scope' => 'namespaced', 'strict' => true],
        'native_constant_invocation' => true,
        'nullable_type_declaration' => false,
        'new_with_parentheses' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],

        'single_line_throw' => false,

        'phpdoc_types' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
