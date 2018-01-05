<?php

return PhpCsFixer\Config::create()
    ->setFinder(
        PhpCsFixer\Finder::create()->in(__DIR__)
    )
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array(
            'syntax' => 'long',
        ),
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_php4_constructor' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'php_unit_strict' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ))
    ->setUsingCache(true)
;
