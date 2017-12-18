<?php

namespace CompilerDesign\Parser;

use Generator;

class Lexer
{
    const NL_UNIX               = "\n";
    const NL_WIN                = "\r\n";
    const IDENTIFIER_MAX_LENGTH = 8;
    const LITERAL_QUOTE         = '"';
    const LITERAL_SPACE         = ' ';
    const LITERAL_TAB           = "\t";
    const LITERAL_UNDERSCORE    = '_';
    const LITERAL_DIEZ          = '#';
    const LITERAL_SEMICOLON     = ';';
    const LITERAL_MINUS         = '-';
    const LITERAL_PLUS          = '+';

    /**
     * @var string
     */
    private $input;
    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $col;

    /**
     * @var int
     */
    private $position;

    /**
     * @var
     */
    private $end;

    private $typeMap;

    private $maxReadBeforeIdentifiers;

    /**
     * Lexer constructor.
     *
     * @param string $input
     * @param array  $typeMap
     */
    public function __construct($input, array $typeMap)
    {
        $this->typeMap                  = $typeMap;
        $this->maxReadBeforeIdentifiers = max(
            array_map(
                'strlen', array_keys($typeMap)
            )
        );
        $this->setInput($input);
    }

    public function setInput(string $input)
    {
        $this->input = $input;
        $this->end   = strlen($input);
    }

    /**
     * @return Generator|Token[]
     */
    public function getTokens()
    {
        $this->reset();
        $this->seekToken();

        while ($this->position < $this->end) {
            list($line, $col) = [$this->line, $this->col];
            $token = $this
                ->parseNext()
                ->setLine($line)
                ->setCol($col);

            echo $token.PHP_EOL;
            $this->seekToken();
            yield $token;
        }
    }

    private function reset()
    {
        $this->line     = 1;
        $this->col      = 1;
        $this->position = 0;
    }

    private function seekToken()
    {
        if (!$this->hasInput()) {
            return;
        }
        $current = $this->current();
        if ($current === self::LITERAL_DIEZ) {
            $this->skipComments();
            $this->seekToken();
        }
        if ($this->isWhitespace($current)) {
            $this->skipWhiteSpace();
            $this->seekToken();
        }
    }

    private function hasInput()
    {
        return $this->position < $this->end - 1;
    }

    private function current()
    {
        return $this->input[$this->position];
    }

    private function skipComments()
    {
        while ($this->hasInput()
            && $this->current() !== self::NL_UNIX
            && $this->current() !== self::NL_WIN
        ) {
            $this->advance();
        }

        if ($this->hasInput()) {
            $this->advance();
        }
    }

    private function advance()
    {
        $prev = $this->input[$this->position];
        $this->position++;
        $this->col++;
        if ($prev === self::NL_UNIX || $prev === self::NL_WIN) {
            $this->line++;
            $this->col = 1;
        }
    }

    private function isWhitespace($char)
    {
        return $char === self::NL_UNIX
            || $char === self::NL_WIN
            || $char === self::LITERAL_TAB
            || $char === self::LITERAL_SPACE;
    }

    private function skipWhiteSpace()
    {
        while ($this->hasInput() && $this->isWhitespace($this->current())) {
            $this->advance();
        }
    }

    /**
     * @return Token
     * @throws Exception
     */
    private function parseNext()
    {
        $currentChar = $this->current();
        if ($currentChar == self::LITERAL_QUOTE) {
            return new Token(Token::T_CONSTANT, $this->parseStringConstant());
        }
        if ($this->isDigit($currentChar)
            || ($this->isSign($currentChar)
                && $this->isDigit($this->peekNext())
            )
        ) {
            return new Token(Token::T_CONSTANT, $this->parseNumberConstant());
        }

        if (null !== $this->peekNext()) {
            // try to match two chars symbols before 1 char symbols
            $twoCharsSymbol = $currentChar.$this->peekNext();
            if (isset($this->typeMap[$twoCharsSymbol])) {
                $this->advance();
                $this->advance();
                $type = $this->typeMap[$twoCharsSymbol];

                return new Token($type, $twoCharsSymbol);
            }
        }

        if (isset($this->typeMap[$currentChar])) {
            $type = $this->typeMap[$currentChar];
            $this->advance();

            return new Token($type, $currentChar);
        }

        $nextValue = $this->parseNextValue();
        if (isset($this->typeMap[$nextValue])) {
            return new Token($this->typeMap[$nextValue], $nextValue);
        }

        // check if identifier
        if ($this->isIdentifier($nextValue)) {
            return new Token(Token::T_IDENTIFIER, $nextValue);
        }

        throw new Exception(
            sprintf(
                'Unrecognized token \'%s\' at %s', $nextValue,
                $this->formatPosition()
            )
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    private function parseStringConstant()
    {
        $stringConst = $this->input[$this->position];
        $this->advance();
        while ($this->hasInput() && $this->current() != self::LITERAL_QUOTE
            && $this->isLetter($this->current())) {
            $stringConst .= $this->current();
            $this->advance();
        }

        $stringConst .= $this->current();
        $this->advance();

        return $stringConst;
    }

    private function isLetter($char)
    {
        return $this->checkCharRange($char, 'a', 'z')
            || $this->checkCharRange($char, 'A', 'Z');
    }

    private function checkCharRange($char, $min, $max)
    {
        return strlen($char) === 1
            && $char >= $min
            && $char <= $max;
    }

    private function isDigit($char)
    {
        return $this->checkCharRange($char, '0', '9');
    }

    private function isSign($char)
    {
        return strlen($char) === 1
            && ($char === self::LITERAL_MINUS || $char === self::LITERAL_PLUS);
    }

    private function peekNext()
    {
        return $this->hasInput() ? $this->input[$this->position + 1] : null;
    }

    private function parseNumberConstant()
    {
        $number = '';
        if ($this->isSign($this->current())) {
            $number .= $this->current();
            $this->advance();
        }

        if ($this->isDigit($this->peekNext())
            && !$this->isNonZeroDigit(
                $this->current()
            )
        ) {
            throw new Exception(
                sprintf(
                    'Invalid numeric constant at %s', $this->formatPosition()
                )
            );
        }
        while ($this->isDigit($this->current())) {
            $number .= $this->current();
            $this->advance();
        }

        return $number;
    }

    private function isNonZeroDigit($char)
    {
        return $this->checkCharRange($char, '1', '9');
    }

    private function formatPosition()
    {
        return sprintf(
            'line: %s, col: %s',
            $this->line,
            $this->col
        );
    }

    private function parseNextValue()
    {
        $value = '';
        while ($this->hasInput()
            && ($this->isLetter($this->current())
                || $this->isDigit($this->current())
                || $this->current() === self::LITERAL_UNDERSCORE
            )) {
            $value .= $this->current();
            $this->advance();
        }

        return $value;
    }

    private function isIdentifier(string $value)
    {
        $len = strlen($value);
        if ($len < 1 || $len > self::IDENTIFIER_MAX_LENGTH) {
            return false;
        }

        if (!($this->isLetter($value[0])
            || $value[0] === self::LITERAL_UNDERSCORE)
        ) {
            return false;
        }
        for ($i = 1; $i < $len; $i++) {
            $c     = $value[$i];
            $valid = $this->isLetter($c);
            $valid = $valid || $this->isDigit($c);
            $valid = $valid || $c === self::LITERAL_UNDERSCORE;
            if (!$valid) {
                return false;
            }
        }

        return true;
    }
}