<?php


namespace CompilerDesign;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class CfgLoader
{
    private $lineSeparator;
    private $productionAssignOperator;
    private $productionsSeparator;
    private $lastSymbol;
    private $epsilonSymbol;
    private $spaceSymbol;

    public function __construct(
        $lineSeparator = PHP_EOL,
        $productionAssignOperator = '->',
        $productionsSeparator = ' ',
        $epsilonSymbol = '__eps__',
        $spaceSymbol = '__space__'
    ) {
        $this->lineSeparator            = $lineSeparator;
        $this->productionAssignOperator = $productionAssignOperator;
        $this->productionsSeparator     = $productionsSeparator;
        $this->epsilonSymbol            = $epsilonSymbol;
        $this->spaceSymbol              = $spaceSymbol;
    }


    public function loadFromString(string $content): ContextFreeGrammar
    {
        $lines = $this->explode($this->lineSeparator, $content);

        $grammar = new ContextFreeGrammar($this->epsilonSymbol);

        foreach ($lines as $line) {
            $this->parseLine($line, $grammar);
        }

        $grammar->extractTerminals();

        return $grammar;
    }

    private function explode(string $separator, string $string)
    {
        return array_filter(
            array_map(
                'trim', explode($separator, $string)
            ),
            function ($item) {
                return strlen($item) != 0;
            }
        );
    }

    private function parseLine(string $line, ContextFreeGrammar $grammar)
    {
        $line = trim($line);
        if ($line[0] == '|') {
            if (null === $this->lastSymbol) {
                throw new \InvalidArgumentException(
                    "Cannot tie production $line to a non terminal."
                );
            }

            $def  = substr($line, 1);
            $line = "$this->lastSymbol $this->productionAssignOperator $def";
        }

        $parts = $this->explode($this->productionAssignOperator, $line);
        if (count($parts) != 2) {
            throw new \InvalidArgumentException(
                "The production $line should contains the symbol on lhs and the production on rhs"
            );
        }

        list($symbol, $production) = $parts;

        $productions = $this->explode($this->productionsSeparator, $production);
        $productions = array_map([$this, 'productionMapper'], $productions);
        $grammar->addRule($symbol, $productions);
        $this->lastSymbol = $symbol;
    }

    private function productionMapper(string $production)
    {
        $production = str_replace($this->spaceSymbol, ' ', $production);
        $len        = strlen($production);
        if ($len > 2 && $production[0] == '"' && $production[$len - 1] == '"') {
            $production = substr($production, 1, $len - 2);
        }

        return $production;
    }
}