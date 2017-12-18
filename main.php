<?php

use CompilerDesign\CfgLoader;
use CompilerDesign\Production;

require_once __DIR__.'/vendor/autoload.php';

$cfgLoader = new CfgLoader();

$grammar = $cfgLoader->loadFromString(
    file_get_contents('data/grammar/cfg2.txt')
);

echo $grammar;