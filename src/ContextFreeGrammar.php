<?php


namespace CompilerDesign;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class ContextFreeGrammar
{
    private $nonTerminals;
    private $terminals;
    private $rulesBySymbol;
    private $startSymbol;
    private $epsilonSymbol;
    private $productionsCount = 0;

    /**
     * @var Rule[]
     */
    private $ruleByOffset;

    public function __construct(string $epsilonSymbol = '__eps__')
    {
        $this->epsilonSymbol = $epsilonSymbol;
    }

    /**
     * @return Rule
     */
    public function getStartSymbolSingleRule(): Rule
    {
        $rules = $this->getRulesBySymbol($this->getStartSymbol());
        if (count($rules) != 1) {
            throw new \RuntimeException("Start symbol does not have just one rule.");
        }

        return reset($rules);
    }

    /**
     * @param string $nonTerminal
     *
     * @return array|Rule[]
     */
    public function getRulesBySymbol(string $nonTerminal): array
    {
        if (!$this->isNonTerminal($nonTerminal)) {
            throw new \InvalidArgumentException(
                "Symbol $nonTerminal is not a non terminal."
            );
        }

        return $this->rulesBySymbol[$nonTerminal];
    }

    public function isNonTerminal(string $value): bool
    {
        return isset($this->nonTerminals[$value]);
    }

    /**
     * @return string
     */
    public function getStartSymbol()
    {
        return $this->startSymbol;
    }

    public function getRuleById(int $id): Rule
    {
        return $this->ruleByOffset[$id];
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
        foreach ($this->rulesBySymbol as $symbol => $rules) {
            $symbolRules = [];
            /** @var Rule[] $rules */
            foreach ($rules as $ruleArray) {
                $symbolRules[] = implode(' ', $ruleArray->getRhs());
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

    public function filterNonTerminals(array $symbols): array
    {
        $filtered = [];
        foreach ($symbols as $symbol) {
            if (is_scalar($symbol) && isset($this->nonTerminals[$symbol])) {
                $filtered[] = $symbol;
            }
        }

        return $filtered;
    }

    public function forEachTerminal(callable $mapper)
    {
        foreach ($this->ruleByOffset as $rule) {
            $rule->mapRhs(
                function ($item) use ($mapper) {
                    if ($this->isNonTerminal($item) || $this->isEpsilon($item)) {
                        return $item;
                    }

                    return $mapper($item);
                }
            );
        }

        $this->extractTerminals();
    }

    public function isEpsilon(string $symbol): bool
    {
        return $this->epsilonSymbol === $symbol;
    }

    public function extractTerminals()
    {
        $this->terminals = [];
        foreach ($this->ruleByOffset as $rule) {
            foreach ($rule->getRhs() as $item) {
                // if it isn't a non terminal then its a terminal
                if (!$this->isNonTerminal($item) && !$this->isEpsilon($item)) {
                    $this->addTerminal($item);
                }
            }
        }
    }

    private function addTerminal(string $value
    ) {
        if ($this->isNonTerminal($value)) {
            throw new \InvalidArgumentException(
                "Variable $value was added as a non terminal."
            );
        }

        $this->terminals[$value] = true;
    }

    public function enrich(): ContextFreeGrammar
    {
        $enriched = clone $this;

        $prevStart             = $enriched->getStartSymbol();
        $newSymbol             = sprintf('_%s', $prevStart);
        $enriched->startSymbol = $newSymbol;
        $enriched->addRule($newSymbol, [$prevStart]);
        $enriched->extractTerminals();

        return $enriched;
    }

    public function addRule($symbol, array $rhs)
    {
        if (empty($this->rulesBySymbol)) {
            $this->startSymbol = $symbol;
        }

        $rule = new Rule($this->productionsCount++, $symbol, $rhs, $this->epsilonSymbol);
        $this->addNonTerminal($symbol);
        $this->rulesBySymbol[$symbol][]     = $rule;
        $this->ruleByOffset[$rule->getId()] = $rule;
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

    public function getNonTerminals()
    {
        return array_keys($this->nonTerminals);
    }


    public function getTerminals()
    {
        return array_keys($this->terminals);
    }
}