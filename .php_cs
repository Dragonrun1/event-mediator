<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('vendor/')
    ->in(__DIR__ . '/src/');

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder($finder);
