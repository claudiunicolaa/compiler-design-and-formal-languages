<?php


namespace CompilerDesign\Analyzer;

use CompilerDesign\Analyzer\Action\AcceptAction;
use CompilerDesign\Analyzer\Action\ActionCollection;
use CompilerDesign\Analyzer\Action\ReduceAction;
use CompilerDesign\Analyzer\Action\ShiftAction;
use CompilerDesign\ContextFreeGrammar;
use CompilerDesign\Parser\Token;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class AnalysisTable
{
    /**
     * @var StateCollection
     */
    private $states;

    /**
     * @var State
     */
    private $initialState;

    /**
     * @var array
     */
    private $gotoTable;

    /**
     * @var ContextFreeGrammar
     */
    private $enrichedGrammar;

    /**
     * @var ActionCollection
     */
    private $actions;

    /**
     * AnalysisTable constructor.
     *
     * @param ContextFreeGrammar $grammar
     * @param StateCollection    $states
     * @param array              $gotoTable
     * @param State              $initialState
     */
    public function __construct(
        ContextFreeGrammar $grammar,
        StateCollection $states,
        array $gotoTable,
        State $initialState
    ) {
        $this->enrichedGrammar = $grammar;
        $this->states          = $states;
        $this->gotoTable       = $gotoTable;
        $this->initialState    = $initialState;
        $this->inferActions();
    }

    private function inferActions()
    {
        $this->actions = new ActionCollection();
        $startSymbol   = $this->enrichedGrammar->getStartSymbol();
        foreach ($this->states as $state) {
            foreach ($state->getAnalysisElements() as $element) {
                $nextSymbol = $element->getSymbolAfterDot();
                if ($nextSymbol !== null && $this->enrichedGrammar->isTerminal($nextSymbol)) {
                    $this->actions->add($state, ShiftAction::get());
                }
                if ($nextSymbol === null && $element->getSymbol() !== $startSymbol) {
                    $this->actions->add($state, new ReduceAction($element));
                }
                if ($nextSymbol === null && $element->getSymbol() === $startSymbol) {
                    $this->actions->add($state, AcceptAction::get());
                }
            }

            if (!$this->actions->has($state->getId())) {
                throw new \RuntimeException("Could not infer action for state $state");
            }
        }
    }

    public function getGoto(State $state, string $symbol): State
    {
        if (isset($this->gotoTable[$state->getId()][$symbol])) {
            return $this->gotoTable[$state->getId()][$symbol];
        }

        $token = Token::getTypeName($symbol);
        throw new \RuntimeException("Undefined goto for state $state and symbol $symbol ($token)");
    }

    /**
     * Retrieve the action for the given state
     *
     * @param State $state
     *
     * @return Action
     */
    public function getAction(State $state)
    {
        return $this->actions->get($state->getId());
    }

    /**
     * @return State
     */
    public function getInitialState(): State
    {
        return $this->initialState;
    }
}