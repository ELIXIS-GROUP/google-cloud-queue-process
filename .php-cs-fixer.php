<?php
$header = <<<EOF
This file is part of the google-cloud-queue-process application.
(c) Anthony Papillaud <apapillaud@elixis.com>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->exclude('vendor')
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'header_comment' => ['header' => $header],
        'phpdoc_var_without_name' => true,
        'logical_operators' => true,
        'phpdoc_separation' => false,
        '@PSR12' => true,
        'phpdoc_align' => ['tags' => ['method', 'param', 'property', 'return', 'throws', 'type', 'var'], 'align' => 'vertical'],
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'allow_unused_params' => false],
        'array_syntax' => ['syntax' => 'short'],
        'semicolon_after_instruction' => true
    ])
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setFinder($finder)
;