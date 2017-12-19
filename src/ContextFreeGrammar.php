<?php


namespace CompilerDesign;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class ContextFreeGrammar
{
    private $nonTerminals;
    private $terminals;
    private $rules;
    private $startSymbol;
    private $epsilonSymbol;

    public function __construct(string $epsilonSymbol = '__eps__')
    {
        $this->epsilonSymbol = $epsilonSymbol;
    }

    public function addRule($symbol, array $rhs)
    {
        if (empty($this->rules)) {
            $this->startSymbol = $symbol;
        }

        $this->addNonTerminal($symbol);
        $this->rules[$symbol][] = $rhs;
    }

    private function addNonTerminal(string $value)
    {
        if ($this->isTerminal($value)) {
            throw new \InvalidArgumentException(
                "Variable $value was already added as a terminal."
            );
        }

        $this->nonTerminals[$value] = true;
    }

    public function isTerminal(string $value): bool
    {
        return isset($this->terminals[$value]);
    }

    public function extractTerminals()
    {
        $this->terminals = [];
        foreach ($this->rules as $symbol => $symbolRules) {
            foreach ($symbolRules as $rule) {
                foreach ($rule as $item) {
                    // if it isn't a non terminal then its a terminal
                    if (!$this->isNonTerminal($item)
                        && !$this->isEpsilon(
                            $item
                        )
                    ) {
                        $this->addTerminal($item);
                    }
                }
            }
        }
    }

    public function isNonTerminal(string $value): bool
    {
        return isset($this->nonTerminals[$value]);
    }

    public function isEpsilon(string $symbol): bool
    {
        return $this->epsilonSymbol === $symbol;
    }

    private function addTerminal(string $value)
    {
        if ($this->isNonTerminal($value)) {
            throw new \InvalidArgumentException(
                "Variable $value was added as a non terminal."
            );
        }

        $this->terminals[$value] = true;
    }

    public function __toString()
    {
        return $this->toString(false);
    }

    public function toString(bool $repeatNonTerminals = true)
    {
        $quote             = function ($val) {
            return "\"$val\"";
        };
        $terminals         = array_map($quote, array_keys($this->terminals));
        $terminals         = implode(', ', $terminals);
        $nonTerminals      = array_keys($this->nonTerminals);
        $upperNonTerminals = array_map('strtoupper', $nonTerminals);
        $nonTerminalsStr   = implode(', ', $upperNonTerminals);
        $productionRules   = [];

        $indentSize = max(array_map('strlen', $nonTerminals));
        $thePad     = str_pad('', $indentSize);
        foreach ($this->rules as $symbol => $rules) {
            $symbolRules = [];
            foreach ($rules as $ruleArray) {
                $symbolRules[] = implode(' ', $ruleArray);
            }

            $symbolPadded = str_pad($symbol, $indentSize, ' ');
            if ($repeatNonTerminals) {
                $thePad = $symbolPadded;
            }
            $tmp               = implode(PHP_EOL."$thePad  | ", $symbolRules);
            $productionRules[] = "$symbolPadded -> $tmp".PHP_EOL;
        }

        $rulesStr = implode(PHP_EOL, $productionRules);
        $rulesStr = str_replace($nonTerminals, $upperNonTerminals, $rulesStr);

        return <<<GRAMMAR
G = (
    TERMINALS     = { $terminals },
    NON-TERMINALS = { $nonTerminalsStr },
    START SYMBOL  = $this->startSymbol
    RULES         = 
$rulesStr
)
GRAMMAR;
    }

    public function forEachTerminal(callable $mapper)
    {
        foreach ($this->rules as $symbol => $rules) {
            foreach ($rules as $ruleId => $rule) {
                foreach ($rule as $itemId => $item) {
                    if (!$this->isNonTerminal($item)
                        && !$this->isEpsilon(
                            $item
                        )
                    ) {
                        $this->rules[$symbol][$ruleId][$itemId] = $mapper(
                            $this->rules[$symbol][$ruleId][$itemId]
                        );
                    }
                }
            }
        }

        $this->extractTerminals();
    }
}