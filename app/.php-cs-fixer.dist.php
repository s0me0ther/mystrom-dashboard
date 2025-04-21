<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor',
    ])
    ->ignoreDotFiles(false)
;

return (new Config())
    ->setRules([
        // default rulesets
        '@PSR12' => true,
        '@PER-CS2.0' => true,
        '@PhpCsFixer' => true,

        // php version rulesets
        '@PHP83Migration' => true,

        // custom rulesets
        'phpdoc_to_comment' => [
            // allow @var /** comments
            'ignored_tags' => ['var'],
        ],
    ])
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setUsingCache(false)
    ->setRiskyAllowed(false)
    ->setFinder($finder)
;
