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
        foreach ($this->analysisElements as $analysisElement) {
            if ($analysisElement->getSymbolAfterDot() === $symbol) {
                return $analysisElement;
            }
        }

        return null;
    }

    public function __toString()
    {
        return sprintf(
            '%s : %s',
            $this->getId(),
            Utils::arrayToString($this->getAnalysisElements())
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