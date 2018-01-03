<?php

use CompilerDesign\Analyzer\LRZeroAnalyzer;

require_once __DIR__.'/vendor/autoload.php';


$loader = new \CompilerDesign\CfgLoader();

//echo $loader->loadFromString(
//    <<<GR
//S -> S S
//S -> ( )
//S -> ( S )
//S -> [ ]
//S -> [ S ]
//GR
//);
//
//echo $loader->loadFromString(
//    <<<GR
//S -> a S a
//S -> b S b
//S -> __eps__
//GR
//);
//
//echo $loader->loadFromString(
//    <<<GR
//S -> T
//S -> S + T
//S -> S - T
//S -> S * T
//S -> S / T
//T -> x
//T -> y
//T -> z
//T -> ( S )
//GR
//);

$grammar = $loader->loadFromString(
    <<<GR
S -> A
A -> a A
A -> b A
A -> __eps__
GR
);

echo $grammar->enrich();

$analyzer = new LRZeroAnalyzer();

$analysisTable = $analyzer->createAnalysisTable($grammar);


//echo PHP_EOL;
//
//foreach ($gotoTable as $stateId => $symbols) {
//    /** @var \CompilerDesign\Analyzer\State $state */
//    foreach ($symbols as $symbol => $state) {
//        echo sprintf(
//            'goto(s%s, %s) = s%s',
//            $stateId,
//            $symbol,
//            $state
//        );
//        echo PHP_EOL;
//    }
//}


$productions = $analyzer->analyze(
    [
        'a',
        'a',
        'b',
        'a',
        'b',
    ],
    $grammar
);

foreach ($productions as $productionId) {
    echo $grammar->getRuleById($productionId).PHP_EOL;
}