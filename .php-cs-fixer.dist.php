<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;
