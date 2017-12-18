<?php

require_once __DIR__.'/vendor/autoload.php';


$loader = new \CompilerDesign\CfgLoader();

echo $loader->loadFromString(
    <<<GR
S -> S S
S -> ( )
S -> ( S )
S -> [ ]
S -> [ S ]
GR
);

echo $loader->loadFromString(
    <<<GR
S -> a S a
S -> b S b
S -> __eps__
GR
);

echo $loader->loadFromString(
    <<<GR
S -> T
S -> S + T
S -> S - T
S -> S * T
S -> S / T
T -> x
T -> y
T -> z
T -> ( S )
GR
);

$tokens = token_get_all(
    <<<'TAG'
<?php $a = 1-2;
TAG
    , TOKEN_PARSE
);


foreach ($tokens as $token) {
    if (is_array($token)) {
        echo "Line {$token[2]}: ", token_name(
            $token[0]
        ), " ('{$token[1]}')", PHP_EOL;
    }
}
print_r($tokens[count($tokens) - 1]);
