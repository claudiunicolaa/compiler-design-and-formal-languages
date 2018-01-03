<?php

namespace CompilerDesign\Parser;

use ReflectionClass;
use RuntimeException;

class Token
{
    const T_INVALID = -1;

    const T_IDENTIFIER = 0;
    const T_CONSTANT   = 1;

    const T_EQUAL            = 10;
    const T_IS_NOT           = 13;
    const T_NOT_EQ           = 16;
    const T_BOOL_AND         = 19;
    const T_BOOL_OR          = 22;
    const T_OPEN_PARAN       = 25;
    const T_CLOSE_PARAN      = 28;
    const T_OPEN_CURLY       = 31;
    const T_CLOSE_CURLY      = 34;
    const T_OPEN_SQUARE      = 37;
    const T_CLOSE_SQUARE     = 40;
    const T_MUL              = 43;
    const T_ADD              = 46;
    const T_SUB              = 49;
    const T_DIV              = 52;
    const T_MOD              = 55;
    const T_GREATER_OR_EQUAL = 58;
    const T_GREATER          = 61;
    const T_LESS             = 64;
    const T_LESS_OR_EQUAL    = 67;
    const T_ASSIGN           = 70;
    const T_SEMICOLON        = 73;
    const T_COMMA            = 76;
    const T_PROGRAM          = 79;
    const T_CONST            = 82;
    const T_DECLARE          = 85;
    const T_AS               = 88;
    const T_RETURN           = 91;
    const T_READ             = 94;
    const T_WRITE            = 97;
    const T_WHILE            = 100;
    const T_IF               = 103;
    const T_ELSE             = 106;
    const T_CHAR             = 109;
    const T_INT              = 112;

    private static $constantsCache;

    private $type;
    private $value;
    private $position;

    public function __construct(int $type, string $value)
    {
        $this->type  = $type;
        $this->value = $value;
    }

    public function __toString()
    {
        return sprintf(
            '\'%s\' (%s) at %s',
            $this->getValue(),
            self::getTypeName($this->getType()),
            $this->getPosition()
        );
    }

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

    public function getType(): int
    {
        return $this->type;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): Token
    {
        $this->position = $position;

        return $this;
    }
}