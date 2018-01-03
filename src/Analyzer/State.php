<?php


namespace CompilerDesign\Analyzer;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class State
{
    private $id;
    private $analysisElements;

    /**
     * State constructor.
     *
     * @param int               $id
     * @param AnalysisElement[] $analysisElements
     */
    public function __construct(int $id, array $analysisElements)
    {
        $this->id               = $id;
        $this->analysisElements = $analysisElements;
    }

    public function findElementWithDotBeforeSymbol(string $symbol)
    {
        /** @var AnalysisElement[] $elements */
        $elements = [];
        foreach ($this->analysisElements as $analysisElement) {
            if ($analysisElement->getSymbolAfterDot() == $symbol) {
                $elements[] = $analysisElement;
            }
        }

        $elementsCount = count($elements);
        if ($elementsCount < 1) {
            return null;
        }
        if ($elementsCount == 1) {
            return reset($elements);
        }


        $elementsStr = implode(PHP_EOL, $elements);
        throw new \RuntimeException(
            "Multiple elements found for symbol $symbol : \n$elementsStr,\n state $this"
        );
    }

    public function __toString()
    {
        return sprintf(
            '%s : %s',
            $this->getId(),
            Utils::arrayToStringSorted($this->getAnalysisElements())
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
     * @return array|AnalysisElement[]
     */
    public function getAnalysisElements()
    {
        return $this->analysisElements;
    }


}