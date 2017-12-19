<?php

namespace CompilerDesign\Parser;

use Exception;
use Generator;

class Lexer
{
    const IDENTIFIER_MAX_LENGTH = 8;

    const NL_UNIX = "\n";
    const NL_WIN  = "\r\n";

    const LITERAL_QUOTE      = '"';
    const LITERAL_SPACE      = ' ';
    const LITERAL_TAB        = "\t";
    const LITERAL_ESCAPE     = "\\";
    const LITERAL_UNDERSCORE = '_';
    const LITERAL_DIEZ       = '#';
    const LITERAL_SEMICOLON  = ';';

    private $input;
    private $line;
    private $col;
    private $offset;
    private $end;
    private $keywords;
    private $symbols;
    private $maxSymbolLength;
    private $errors;

    public function __construct(array $symbols, array $keywords)
    {
        $this->symbols         = $symbols;
        $this->keywords        = $keywords;
        $this->maxSymbolLength = max(array_map('strlen', $symbols));
    }

    /**
     * @param string $input
     *
     * @return Generator|Token[]
     */
    public function getTokens(string $input): Generator
    {
        $this->setInput($input);

        $this->reset();
        $this->seekNext();

        while ($this->hasInput()) {
            $tokenPosition = $this->currentPosition();
            $token         = $this->parseNext()->setPosition($tokenPosition);

            $this->seekNext();
            yield $token;
        }
    }

    private function setInput(string $input)
    {
        $this->input  = $input;
        $this->end    = strlen($input);
        $this->errors = [];
    }

    private function reset()
    {
        $this->line   = 1;
        $this->col    = 1;
        $this->offset = 0;
    }

    /**
     * Move the internal offset up to the next possible token
     * by skipping comments and whitespaces
     */
    private function seekNext()
    {
        if (!$this->hasInput()) {
            return;
        }

        $current = $this->current();
        if ($current === self::LITERAL_DIEZ) {
            $this->skipCurrentLine();
            $this->seekNext();
        }

        if ($this->isWhitespace($current)) {
            $this->skipWhiteSpaces();
            $this->seekNext();
        }
    }

    private function hasInput(): bool
    {
        return $this->offset < $this->end;
    }

    private function current(): string
    {
        return $this->input[$this->offset];
    }

    /**
     * Moves the internal offset to the beginning of the next line
     */
    private function skipCurrentLine()
    {
        while ($this->hasInput() && !$this->isNewLine($this->current())) {
            $this->advance();
        }

        if ($this->hasInput()) {
            $this->advance();
        }
    }

    private function isNewLine(string $char): bool
    {
        return $char === self::NL_WIN || $char === self::NL_UNIX;
    }

    /**
     * Move the internal offset with the given position
     * and update other internals accordingly
     *
     * @param int $howMuch
     */
    private function advance(int $howMuch = 1)
    {
        if ($howMuch <= 0 || !isset($this->input[$this->offset])) {
            return;
        }

        $prev = $this->input[$this->offset];
        ++$this->offset;
        ++$this->col;
        if ($this->isNewLine($prev)) {
            ++$this->line;
            $this->col = 1;
        }
        $this->advance($howMuch - 1);
    }

    private function isWhitespace(string $char): bool
    {
        return $this->isNewLine($char)
            || $char === self::LITERAL_TAB
            || $char === self::LITERAL_SPACE;
    }

    private function skipWhiteSpaces()
    {
        while ($this->hasInput() && $this->isWhitespace($this->current())) {
            $this->advance();
        }
    }

    private function currentPosition(): Position
    {
        return new Position($this->line, $this->col);
    }

    /**
     * @return Token
     * @throws Exception
     */
    private function parseNext(): Token
    {
        $currentChar = $this->current();
        if ($currentChar == self::LITERAL_QUOTE) {
            return $this->parseStringConstant();
        }
        if ($this->isDigit($currentChar)) {
            return $this->parseNumberConstant();
        }

        for ($i = $this->maxSymbolLength; $i > 0; $i--) {
            $symbolPeek = $currentChar.$this->peekNext($i);
            if (isset($this->symbols[$symbolPeek])) {
                $this->advance($i + 1);

                return new Token($this->symbols[$symbolPeek], $symbolPeek);
            }
        }

        // also try to match one char symbols
        if (isset($this->symbols[$currentChar])) {
            // move the pointer to the next character
            $this->advance();

            return new Token($this->symbols[$currentChar], $currentChar);
        }

        $startPosition = $this->currentPosition();
        $nextValue     = $this->parseIdentifier();

        // an identifier might also be a keyword
        // so do a check before assuming the token is an identifier
        $lowerNextValue = strtolower($nextValue);
        if (isset($this->keywords[$lowerNextValue])) {
            return new Token($this->keywords[$lowerNextValue], $nextValue);
        }

        // check if identifier
        if ($this->isIdentifier($nextValue)) {
            return new Token(Token::T_IDENTIFIER, $nextValue);
        }

        $nextValue .= $this->seekWhiteSpaceOrSymbol();
        $this->error($startPosition, "Unrecognized symbol '$nextValue'.");

        return new Token(Token::T_INVALID, $nextValue);
    }

    /**
     * @return Token
     * @throws Exception
     */
    private function parseStringConstant(): Token
    {
        $start       = $this->currentPosition();
        $stringConst = $this->currentAndAdvance();

        // read until find the closing quote
        while ($this->hasInput()
            && $this->current() != self::LITERAL_QUOTE
            && $this->isValidStringChar($this->current())) {
            $stringConst .= $this->currentAndAdvance();
        }

        if ($this->current() !== self::LITERAL_QUOTE) {
            $this->error($start, "Missing closing quote opened.");

            return new Token(Token::T_INVALID, $stringConst);
        }

        $stringConst .= $this->currentAndAdvance();

        return new Token(Token::T_CONSTANT, $stringConst);
    }

    /**
     * Retrieve the char at the current position and move
     * the internal offset to the next position
     *
     * @return string
     */
    private function currentAndAdvance(): string
    {
        $current = $this->current();
        $this->advance();

        return $current;
    }

    private function isValidStringChar(string $char): bool
    {
        return $this->isLetter($char)
            || $this->isDigit($char)
            || $this->isWhitespace($char);
    }

    private function isLetter($char): bool
    {
        return $this->isCharRange($char, 'a', 'z')
            || $this->isCharRange($char, 'A', 'Z');
    }

    private function isCharRange(string $char, string $min, string $max): bool
    {
        return strlen($char) === 1
            && $char >= $min
            && $char <= $max;
    }

    private function isDigit($char): bool
    {
        return $this->isCharRange($char, '0', '9');
    }

    private function error(Position $position, string $message)
    {
        $this->errors[] = "$position: $message";
    }

    private function parseNumberConstant(): Token
    {
        // validate that is is a multiple digits number
        // the first digit is not zero
        if ($this->isDigit($this->peekNext())
            && !$this->isNonZeroDigit($this->current())
        ) {
            $this->error($this->currentPosition(), "Invalid numeric constant");

            return new Token(Token::T_INVALID, $this->currentAndAdvance());
        }

        $number = '';
        while ($this->hasInput() && $this->isDigit($this->current())) {
            $number .= $this->currentAndAdvance();
        }

        return new Token(Token::T_CONSTANT, $number);
    }

    /**
     * Take a look at the next char sequence without
     * changing the internal pointer
     *
     * @param int $peekCount
     *
     * @return string
     */
    private function peekNext(int $peekCount = 1): string
    {
        $peek            = '';
        $peekCurrentSize = 0;
        while (isset($this->input[$this->offset + $peekCurrentSize + 1])
            && $peekCurrentSize < $peekCount) {
            $peek .= $this->input[$this->offset + $peekCurrentSize + 1];
            ++$peekCurrentSize;
        }

        return $peek;
    }

    private function isNonZeroDigit(string $char): bool
    {
        return $this->isCharRange($char, '1', '9');
    }

    /**
     * Try to parse an identifier, i.e. a sequence of characters accepted
     * for identifiers
     *
     * @return string
     */
    private function parseIdentifier(): string
    {
        $value = '';
        while ($this->hasInput()
            && (
                $this->isLetter($this->current())
                || $this->isDigit($this->current())
                || $this->current() == self::LITERAL_UNDERSCORE
            )) {
            $value .= $this->currentAndAdvance();
        }

        return $value;
    }

    /**
     * Self explanatory
     *
     * @param string $val
     *
     * @return bool
     */
    private function isIdentifier(string $val): bool
    {
        $len = strlen($val);
        if ($len < 1 || $len > self::IDENTIFIER_MAX_LENGTH) {
            return false;
        }

        if (!$this->isLetterOrUnderscore($val[0])) {
            return false;
        }

        for ($i = 1; $i < $len; ++$i) {
            $c = $val[$i];
            if (!($this->isLetterOrUnderscore($c) || $this->isDigit($c))) {
                return false;
            }
        }

        return true;
    }

    private function isLetterOrUnderscore(string $char): bool
    {
        return $this->isLetter($char) || $char === self::LITERAL_UNDERSCORE;
    }

    /**
     * Move the internal offset to the next whitespace or symbol
     * and return the skipped characters
     *
     * @return string
     */
    private function seekWhiteSpaceOrSymbol(): string
    {
        $skipped = '';
        while ($this->hasInput()) {

            if ($this->isWhitespace($this->current())) {
                break;
            }
            if (isset($this->symbols[$this->current()])) {
                break;
            }

            $skipped .= $this->currentAndAdvance();
        }

        return $skipped;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}