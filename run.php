<?php

use CompilerDesign\Analyzer\LRZeroAnalyzer;
use CompilerDesign\CfgLoader;
use CompilerDesign\Parser\Scanner;
use CompilerDesign\Parser\Token;

require_once __DIR__.'/vendor/autoload.php';


$file = __DIR__.'/data/input/simple.txt';

$scanner = new Scanner();

$input = file_get_contents($file);
$scanner->scan($input);

$cfgLoader = new CfgLoader();
$grammar   = $cfgLoader->loadFromString(
    file_get_contents('data/grammar/cfg2.txt')
);

$scanner->replaceTerminals($grammar);

$tokens      = $scanner->getTokens($input);
$tokenTypes = array_map(
    function (Token $token) {
        return $token->getType();
    },
    $tokens
);
echo implode(PHP_EOL, $tokens).PHP_EOL;
echo implode(PHP_EOL, $tokenTypes).PHP_EOL;

$analyzer        = new LRZeroAnalyzer();
$productionsUsed = $analyzer->analyze($tokenTypes, $grammar);

foreach ($productionsUsed as $productionId) {
    echo $grammar->getRuleById($productionId).PHP_EOL;
}