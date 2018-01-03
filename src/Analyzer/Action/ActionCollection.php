<?php


namespace CompilerDesign\Analyzer\Action;

use CompilerDesign\Analyzer\Action;
use CompilerDesign\Analyzer\State;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class ActionCollection
{
    /**
     * @var Action[]
     */
    private $actions = [];

    public function add(State $state, Action $action)
    {
        if (isset($this->actions[$state->getId()])) {
            $previous = $this->get($state->getId());
            if ($previous instanceof ShiftAction && $action instanceof ShiftAction) {
                return;
            }

            if ($previous instanceof ShiftAction && $action instanceof ReduceAction) {
                throw new \RuntimeException("Shift-reduce conflict detected for state $state");
            }
            if ($previous instanceof ReduceAction && $action instanceof ReduceAction) {
                throw new \RuntimeException("Shift-shift conflict detected for state $state");
            }

            throw new \RuntimeException(sprintf(
                'Conflict detected for state %s, %s with %s',
                $state,
                get_class($previous),
                get_class($action)
            ));
        }

        $this->actions[$state->getId()] = $action;
    }

    public function get(int $stateId)
    {
        return $this->actions[$stateId];
    }

    public function has($stateId)
    {
        return isset($this->actions[$stateId]);
    }
}