<?php

namespace CompilerDesign\Parser;

use ReflectionClass;
use RuntimeException;

class Token
{
    const T_UNKNOWN = -1;

    const T_IDENTIFIER = 0;
    const T_CONSTANT   = 1;

    const T_EQUAL            = 2;
    const T_IS_NOT           = 3;
    const T_NOT_EQ           = 4;
    const T_BOOL_AND         = 5;
    const T_BOOL_OR          = 6;
    const T_OPEN_PARAN       = 7;
    const T_CLOSE_PARAN      = 8;
    const T_OPEN_CURLY       = 9;
    const T_CLOSE_CURLY      = 10;
    const T_OPEN_SQUARE      = 11;
    const T_CLOSE_SQUARE     = 12;
    const T_MUL              = 13;
    const T_ADD              = 14;
    const T_SUB              = 15;
    const T_DIV              = 16;
    const T_MOD              = 17;
    const T_GREATER_OR_EQUAL = 18;
    const T_GREATER          = 19;
    const T_LESS             = 20;
    const T_LESS_OR_EQUAL    = 21;
    const T_ASSIGN           = 22;
    const T_SEMICOLON        = 23;
    const T_COMMA            = 24;
    const T_PROGRAM          = 25;
    const T_CONST            = 26;
    const T_DECLARE          = 27;
    const T_AS               = 28;
    const T_RETURN           = 29;
    const T_READ             = 30;
    const T_WRITE            = 31;
    const T_WHILE            = 32;
    const T_IF               = 33;
    const T_ELSE             = 34;
    const T_CHAR             = 35;
    const T_INT              = 36;

    private static $constantsCache;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $col;

    /**
     * Token constructor.
     *
     * @param int   $type
     * @param string $value
     */
    public function __construct(int $type, string $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf(
            '\'%s\' (%s) at line: %s, col: %s',
            $this->getValue(),
            self::getTypeName($this->getType()),
            $this->getLine(),
            $this->getCol()
        );
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public static function getTypeName(int $type): string
    {
        if (self::$constantsCache === null) {
            $tokenClass        = new ReflectionClass(Token::class);
            $constants         = $tokenClass->getConstants();
            $initialCount      = count($constants);
            $reversedConstants = array_flip($constants);
            if (count($reversedConstants) !== $initialCount) {
                throw new RuntimeException("Duplicate constant value.");
            }

            self::$constantsCache = $reversedConstants;
        }

        if (!isset(self::$constantsCache[$type])) {
            throw new RuntimeException(
                "Undefined constant for value [$type]."
            );
        }

        return self::$constantsCache[$type];
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @param int $line
     *
     * @return Token
     */
    public function setLine(int $line): Token
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return int
     */
    public function getCol(): int
    {
        return $this->col;
    }

    /**
     * @param int $col
     *
     * @return Token
     */
    public function setCol(int $col): Token
    {
        $this->col = $col;

        return $this;
    }
}