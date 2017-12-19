<?php

$tokens
    = <<<TOKENS
equal
is_not
not_eq
bool_and
bool_or
open_paran
close_paran
open_curly
close_curly
open_square
close_square
mul
add
sub
div
mod
greater_or_equal
greater
less
less_or_equal
assign
semicolon
comma
program
const
declare
as
return
read
write
while
if
else
char
int
TOKENS;

$tokens = explode(PHP_EOL, $tokens);
$cnt    = 10;
foreach ($tokens as $token) {
    $up = strtoupper($token);
    echo "const T_$up = $cnt;".PHP_EOL;
    $cnt += 3;
}

echo '===================='.PHP_EOL;

$cnt = 2;
foreach ($tokens as $token) {
    $up = strtoupper($token);
    echo "'td' => Token::T_$up,".PHP_EOL;
}