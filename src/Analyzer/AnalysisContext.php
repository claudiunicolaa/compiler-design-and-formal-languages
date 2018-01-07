<?php


namespace CompilerDesign\Analyzer;

use CompilerDesign\Analyzer\Action\AcceptAction;
use CompilerDesign\Analyzer\Action\ErrorAction;
use CompilerDesign\Analyzer\Action\ReduceAction;
use CompilerDesign\Analyzer\Action\ShiftAction;
use CompilerDesign\Rule;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class AnalysisContext
{
    /**
     * @var array
     */
    private $input;

    /**
     * @var AnalysisTable
     */
    private $analysisTable;

    /**
     * @var bool
     */
    private $finished = false;

    private $position = 0;

    private $inputSize;

    /**
     * @var \SplStack
     */
    private $productionsUsed;

    private $stack;

    private $currentState;

    /**
     * AnalysisContext constructor.
     *
     * @param array         $input
     * @param AnalysisTable $analysisTable
     */
    public function __construct(array $input, AnalysisTable $analysisTable)
    {
        $this->input           = $input;
        $this->analysisTable   = $analysisTable;
        $this->productionsUsed = new \SplStack();
        $this->stack           = new \SplStack();
        $this->currentState    = $analysisTable->getInitialState();
        $this->inputSize       = count($input);
        $this->stack->push($this->currentState);
    }

    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * @return array
     */
    public function getProductionsUsed(): array
    {
        $this->productionsUsed->setIteratorMode(\SplStack::IT_MODE_LIFO);

        return iterator_to_array($this->productionsUsed);
    }

    public function applyNextAction()
    {
        $action = $this->analysisTable->getAction($this->currentState);
        if ($action instanceof ShiftAction) {
            if (!isset($this->input[$this->position])) {
                throw new \RuntimeException('Input finished without being valid.');
            }

            $currentSymbol = $this->input[$this->position];
            $this->stack->push($currentSymbol);
            $this->currentState = $this->analysisTable->getGoto(
                $this->currentState, $currentSymbol
            );
            $this->stack->push($this->currentState);
            $this->position++;
        } elseif ($action instanceof ReduceAction) {
            $rule = $action->getAnalysisElement()->getRule();
            $this->popRule($rule);
            $temp = $this->stack->top();
            $this->stack->push($rule->getNonTerminal());
            $this->currentState = $this->analysisTable->getGoto($temp, $rule->getNonTerminal());
            $this->stack->push($this->currentState);
            $this->productionsUsed->push($rule->getId());
        } elseif ($action instanceof AcceptAction) {
            $this->finishAnalysis();
        } elseif ($action instanceof ErrorAction) {
            throw new \RuntimeException('Analysis failed');
        }
    }

    private function popRule(Rule $rule)
    {
        $symbols = $rule->getRhs();
        for ($i = count($symbols) - 1; $i >= 0; --$i) {
            $this->stack->pop();
            if ($symbols[$i] != $this->stack->pop()) {
                throw new \RuntimeException(
                    "Mismatch between the stack and rule $rule"
                );
            }
        }
    }

    public function finishAnalysis()
    {
        $this->finished = true;
    }
}