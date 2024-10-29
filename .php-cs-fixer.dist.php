<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'single_line_empty_body' => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;
