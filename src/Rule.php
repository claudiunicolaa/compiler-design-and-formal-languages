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
    private $epsilonSymbol;

    public function __construct(int $id, string $nonTerminal, array $rhs, string $epsilonSymbol)
    {
        $this->id            = $id;
        $this->nonTerminal   = $nonTerminal;
        $this->rhs           = $rhs;
        $this->epsilonSymbol = $epsilonSymbol;
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

    public function getRhsWithoutEpsilon()
    {
        $filterEpsilon = function ($item) {
            return $item !== $this->epsilonSymbol;
        };

        return array_filter($this->getRhs(), $filterEpsilon);
    }
}