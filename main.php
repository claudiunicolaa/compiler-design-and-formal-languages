<?php

use CompilerDesign\CfgLoader;
use CompilerDesign\Parser\Scanner;
use CompilerDesign\Production;

require_once __DIR__.'/vendor/autoload.php';

$cfgLoader = new CfgLoader();

$grammar = $cfgLoader->loadFromString(
    file_get_contents('data/grammar/cfg2.txt')
);

$scanner = new Scanner('');
$scanner->replaceTerminals($grammar);

echo $grammar;