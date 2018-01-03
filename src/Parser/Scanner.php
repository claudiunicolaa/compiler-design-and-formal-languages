<?php

namespace CompilerDesign\Parser;

use CompilerDesign\ContextFreeGrammar;

class Scanner
{
    const CODE_SYMBOL_OR_KEYWORD = -1;

    const IDENTIFIER_PLACEHOLDER = '__IDENTIFIER__';
    const CONSTANT_PLACEHOLDER   = '__CONSTANT__';

    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var array
     */
    private $codifiedTable;

    /**
     * The symbols will be matched starting with the one with the highest
     * length of the one with length one
     *
     * @var array
     */
    private $symbols
        = [
            '==' => Token::T_EQUAL,
            '!'  => Token::T_IS_NOT,
            '!=' => Token::T_NOT_EQ,
            '&&' => Token::T_BOOL_AND,
            '||' => Token::T_BOOL_OR,
            '('  => Token::T_OPEN_PARAN,
            ')'  => Token::T_CLOSE_PARAN,
            '{'  => Token::T_OPEN_CURLY,
            '}'  => Token::T_CLOSE_CURLY,
            '['  => Token::T_OPEN_SQUARE,
            ']'  => Token::T_CLOSE_SQUARE,
            '*'  => Token::T_MUL,
            '+'  => Token::T_ADD,
            '-'  => Token::T_SUB,
            '/'  => Token::T_DIV,
            '%'  => Token::T_MOD,
            '>=' => Token::T_GREATER_OR_EQUAL,
            '>'  => Token::T_GREATER,
            '<'  => Token::T_LESS,
            '<=' => Token::T_LESS_OR_EQUAL,
            '='  => Token::T_ASSIGN,
            ';'  => Token::T_SEMICOLON,
            ','  => Token::T_COMMA,
        ];

    /**
     * Keywords will be matched without taking into consideration
     * the casing
     *
     * @var array
     */
    private $keywords
        = [
            'program' => Token::T_PROGRAM,
            'const'   => Token::T_CONST,
            'declare' => Token::T_DECLARE,
            'as'      => Token::T_AS,
            'return'  => Token::T_RETURN,
            'read'    => Token::T_READ,
            'write'   => Token::T_WRITE,
            'while'   => Token::T_WHILE,
            'if'      => Token::T_IF,
            'else'    => Token::T_ELSE,
            'char'    => Token::T_CHAR,
            'int'     => Token::T_INT,
        ];

    /**
     * @var array
     */
    private $internalForm;

    /**
     * @var SymbolTable
     */
    private $identifiersTable;

    /**
     * @var SymbolTable
     */
    private $constantsTable;

    /**
     * Scanner constructor.
     */
    public function __construct()
    {
        $this->codifiedTable = array_merge($this->symbols, $this->keywords);
        $this->lexer         = new Lexer($this->symbols, $this->keywords);
    }

    public function getTokens(string $input)
    {
        return iterator_to_array($this->lexer->getTokens($input));
    }

    public function scan(string $input)
    {
        $this->internalForm     = [];
        $this->identifiersTable = new SymbolTable();
        $this->constantsTable   = new SymbolTable();

        foreach ($this->lexer->getTokens($input) as $token) {
            switch ($token->getType()) {
                case Token::T_IDENTIFIER:
                    $code = $this->identifiersTable->put($token->getValue());
                    break;
                case Token::T_CONSTANT:
                    $code = $this->constantsTable->put($token->getValue());
                    break;
                default:
                    $code = self::CODE_SYMBOL_OR_KEYWORD;
            }
            $this->internalForm[] = [$token->getType(), $code];
        }

        $errors = $this->lexer->getErrors();
        if (!empty($errors)) {
            throw new LexicalException(implode(PHP_EOL, $errors));
        }
    }

    /**
     * @return array
     */
    public function getInternalForm(): array
    {
        return $this->internalForm;
    }

    /**
     * @return SymbolTable
     */
    public function getIdentifiers(): SymbolTable
    {
        return $this->identifiersTable;
    }

    /**
     * @return SymbolTable
     */
    public function getConstants(): SymbolTable
    {
        return $this->constantsTable;
    }

    public function replaceTerminals(ContextFreeGrammar $grammar)
    {
        $itemMap = array_merge(
            $this->codifiedTable,
            [
                self::IDENTIFIER_PLACEHOLDER => Token::T_IDENTIFIER,
                self::CONSTANT_PLACEHOLDER   => Token::T_CONSTANT,
            ]
        );

        $grammar->forEachTerminal(
            function ($item) use ($itemMap) {
                if (isset($itemMap[$item])) {
                    return $itemMap[$item];
                }

                return $item;
            }
        );
    }
}