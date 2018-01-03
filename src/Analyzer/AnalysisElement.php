<?php


namespace CompilerDesign\Analyzer;

use CompilerDesign\ContextFreeGrammar;
use CompilerDesign\Rule;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class AnalysisElement
{
    private $symbol;
    private $rhs;
    private $dotPosition;
    private $rhsSize;
    private $rule;

    private function __construct(string $symbol, array $rhs, Rule $rule)
    {
        $this->symbol      = $symbol;
        $this->rhs         = $rhs;
        $this->dotPosition = array_search(Dot::get(), $this->rhs, true);
        $this->rhsSize     = count($rhs);
        $this->rule      = $rule;
    }

    public static function fromRule(Rule $rule)
    {
        return new self(
            $rule->getNonTerminal(),
            array_merge([Dot::get()], $rule->getRhsWithoutEpsilon()),
            $rule
        );
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @return array
     */
    public function getRhs(): array
    {
        return $this->rhs;
    }

    public function withDotShifted(): AnalysisElement
    {
        $element = clone $this;
        if ($this->dotPosition === $this->rhsSize - 1) {
            throw new \RuntimeException(
                "Cannot shift the dot at the last position for element $element"
            );
        } else {
            $element->rhs[$element->dotPosition]     = $element->rhs[$element->dotPosition + 1];
            $element->rhs[$element->dotPosition + 1] = Dot::get();
            $element->dotPosition++;
        }

        return $element;
    }

    public function getSymbolBeforeDot()
    {
        return $this->getRhsSymbolOrNull($this->dotPosition - 1);
    }

    private function getRhsSymbolOrNull(int $position)
    {
        return isset($this->rhs[$position]) ? $this->rhs[$position] : null;
    }

    public function getSymbolAfterDot()
    {
        return $this->getRhsSymbolOrNull($this->dotPosition + 1);
    }

    public function __toString()
    {
        return $this->hash();
    }

    public function hash()
    {
        $rhs = implode(' ', $this->rhs);

        return "[$this->symbol -> $rhs]";
    }

    /**
     * @return int
     */
    public function getRhsSize(): int
    {
        return $this->rhsSize;
    }

    /**
     * @return Rule
     */
    public function getRule(): Rule
    {
        return $this->rule;
    }
}