#!/usr/bin/env php
<?php
// application.php

use CompilerDesign\Parser\Scanner;

require __DIR__.'/vendor/autoload.php';

$file = __DIR__.'/data/input/input4-new.txt';

$scanner = new Scanner();

$input = file_get_contents($file);
$scanner->scan($input);

$output = 'Constants Table'.PHP_EOL;
foreach ($scanner->getConstants()->toArray() as $const => $code) {
    $output .= "$const $code".PHP_EOL;
}
$output .= PHP_EOL."Identifiers table".PHP_EOL;
foreach ($scanner->getIdentifiers()->toArray() as $id => $code) {
    $output .= "$id $code".PHP_EOL;
}
$output .= PHP_EOL."Internal form".PHP_EOL;
foreach ($scanner->getInternalForm() as $item) {
    $output .= implode(' ', $item).PHP_EOL;
}
$output .= PHP_EOL;

file_put_contents($file.'.out', $output);
file_put_contents(
    $file.'.tokens', implode(PHP_EOL, $scanner->getTokens($input))
);
