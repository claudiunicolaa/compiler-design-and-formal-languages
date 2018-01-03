<?php


namespace CompilerDesign;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class Rule
{
    private $id;
    private $nonTerminal;
    private $rhs;

    public function __construct(int $id, string $nonTerminal, array $rhs)
    {
        $this->id          = $id;
        $this->nonTerminal = $nonTerminal;
        $this->rhs         = $rhs;
    }

    public function __toString()
    {
        return sprintf(
            '(%d) %s -> %s',
            $this->getId(),
            $this->getNonTerminal(),
            implode(' ', $this->getRhs())
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNonTerminal(): string
    {
        return $this->nonTerminal;
    }

    /**
     * @return array
     */
    public function getRhs(): array
    {
        return $this->rhs;
    }

    public function mapRhs(callable $mapper)
    {
        $this->rhs = array_map($mapper, $this->rhs);
    }
}