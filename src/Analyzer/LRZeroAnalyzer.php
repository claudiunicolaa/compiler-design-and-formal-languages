<?php


namespace CompilerDesign\Analyzer;

use CompilerDesign\ContextFreeGrammar;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class LRZeroAnalyzer
{
    /**
     * Analyze a sequence against the given grammar
     *
     * @param array              $tokens  The sequenced tokens to analyze
     * @param ContextFreeGrammar $grammar The grammar
     *
     * @return array The productions used
     */
    public function analyze(array $tokens, ContextFreeGrammar $grammar)
    {
        $analysisContext = new AnalysisContext($tokens, $this->createAnalysisTable($grammar));

        while (!$analysisContext->isFinished()) {
            $analysisContext->applyNextAction();
        }

        return $analysisContext->getProductionsUsed();
    }

    public function createAnalysisTable(ContextFreeGrammar $grammar)
    {
        $enrichedGrammar = $grammar->enrich();

        $states       = new StateCollection();
        $gotoTable    = [];
        $cnt          = 0;
        $firstElement = AnalysisElement::fromRule($enrichedGrammar->getStartSymbolSingleRule());
        $initialState = new State($cnt++, $this->closure($firstElement, $enrichedGrammar));
        $states->add($initialState);

        $modified = true;
        $symbols  = array_merge(
            $enrichedGrammar->getNonTerminals(),
            $enrichedGrammar->getTerminals()
        );

        while ($modified) {
            $modified = false;
            foreach ($states as $state) {
                foreach ($symbols as $symbol) {
                    if (isset($gotoTable[$state->getId()][$symbol])) {
                        continue;
                    }

                    $goto = $this->computeGoto($state, $symbol, $enrichedGrammar);
                    if ($goto !== null) {
                        $existingState = $states->findStateWithElements($goto);
                        if ($existingState === null) {
                            $modified = true;
                            $newState = new State($cnt++, $goto);
                            $states->add($newState);
                            $gotoTable[$state->getId()][$symbol] = $newState;
                        } else {
                            $gotoTable[$state->getId()][$symbol] = $existingState;
                        }
                    }
                }
            }
        }

        return new AnalysisTable($enrichedGrammar, $states, $gotoTable, $initialState);
    }

    /**
     * @param AnalysisElement    $element
     * @param ContextFreeGrammar $grammar
     *
     * @return AnalysisElement[]
     */
    private function closure(AnalysisElement $element, ContextFreeGrammar $grammar)
    {
        /** @var AnalysisElement[] $closure */
        $closure  = [$element->hash() => $element];
        $modified = true;
        while ($modified) {
            $modified = false;
            foreach ($closure as $elem) {
                $symbol = $elem->getSymbolAfterDot();
                if ($symbol !== null && $grammar->isNonTerminal($symbol)) {
                    foreach ($grammar->getRulesBySymbol($symbol) as $rule) {
                        $newElem = AnalysisElement::fromRule($rule);
                        $key     = $newElem->hash();
                        if (!isset($closure[$key])) {
                            $closure[$key] = $newElem;
                            $modified      = true;
                        }
                    }
                }
            }
        }

        return $closure;
    }

    /**
     * @param State              $state
     * @param string             $symbol A terminal or non terminal
     * @param ContextFreeGrammar $grammar
     *
     * @return AnalysisElement[]
     */
    public function computeGoto(State $state, string $symbol, ContextFreeGrammar $grammar)
    {
        $element = $state->findElementWithDotBeforeSymbol($symbol);
        if ($element === null) {
            return null;
        }

        return $this->closure(
            $element->withDotShifted(),
            $grammar
        );
    }
}

