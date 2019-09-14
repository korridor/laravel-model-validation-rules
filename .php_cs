<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(false)
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'protected_to_private' => false,
        // Part of future @Symfony ruleset in PHP-CS-Fixer To be removed from the config file once upgrading
        'phpdoc_types_order' => ['null_adjustment' => 'always_last', 'sort_algorithm' => 'none'],
        // custom
        'phpdoc_separation' => false,
        'phpdoc_align' => false,
        'phpdoc_no_alias_tag' => false,
        'array_indentation' => true,
    ])
    ->setUsingCache(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;
