<?php


namespace CompilerDesign\Analyzer\Action;

use CompilerDesign\Analyzer\Action;
use CompilerDesign\Analyzer\AnalysisContext;
use CompilerDesign\Analyzer\AnalysisElement;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class ReduceAction implements Action
{
    /**
     * @var AnalysisElement
     */
    private $analysisElement;

    /**
     * ReduceAction constructor.
     *
     * @param AnalysisElement $analysisElement
     */
    public function __construct(AnalysisElement $analysisElement)
    {
        $this->analysisElement = $analysisElement;
    }

    /**
     * @return AnalysisElement
     */
    public function getAnalysisElement(): AnalysisElement
    {
        return $this->analysisElement;
    }
}