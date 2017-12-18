<?php

namespace CompilerDesign\Parser;

class Scanner
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var array
     */
    private $codifiedTable
        = [
            '=='      => Token::T_EQUAL,
            '!'       => Token::T_IS_NOT,
            '!='      => Token::T_NOT_EQ,
            '&&'      => Token::T_BOOL_AND,
            '||'      => Token::T_BOOL_OR,
            '('       => Token::T_OPEN_PARAN,
            ')'       => Token::T_CLOSE_PARAN,
            '{'       => Token::T_OPEN_CURLY,
            '}'       => Token::T_CLOSE_CURLY,
            '['       => Token::T_OPEN_SQUARE,
            ']'       => Token::T_CLOSE_SQUARE,
            '*'       => Token::T_MUL,
            '+'       => Token::T_ADD,
            '-'       => Token::T_SUB,
            '/'       => Token::T_DIV,
            '%'       => Token::T_MOD,
            '>='      => Token::T_GREATER_OR_EQUAL,
            '>'       => Token::T_GREATER,
            '<'       => Token::T_LESS,
            '<='      => Token::T_LESS_OR_EQUAL,
            '='       => Token::T_ASSIGN,
            ';'       => Token::T_SEMICOLON,
            ','       => Token::T_COMMA,
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
     * @var Container
     */
    private $identifiersTable;

    /**
     * @var Container
     */
    private $constantsTable;

    /**
     * Scanner constructor.
     *
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->lexer = new Lexer($input, $this->codifiedTable);
    }

    public function getTokens()
    {
        return iterator_to_array($this->lexer->getTokens());
    }

    public function scan()
    {
        $this->internalForm     = [];
        $this->identifiersTable = new Container();
        $this->constantsTable   = new Container();

        foreach ($this->lexer->getTokens() as $token) {
            switch ($token->getType()) {
                case Token::T_IDENTIFIER:
                    $code = $this->identifiersTable->put($token->getValue());
                    break;
                case Token::T_CONSTANT:
                    $code = $this->constantsTable->put($token->getValue());
                    break;
                default:
                    $code = -1;
            }
            $this->internalForm[] = [$token->getType(), $code];
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
     * @return Container
     */
    public function getIdentifiers(): Container
    {
        return $this->identifiersTable;
    }

    /**
     * @return Container
     */
    public function getConstants(): Container
    {
        return $this->constantsTable;
    }
}