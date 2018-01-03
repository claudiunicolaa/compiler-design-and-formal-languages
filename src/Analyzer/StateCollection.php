<?php


namespace CompilerDesign\Analyzer;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class StateCollection implements \IteratorAggregate
{
    private $states = [];

    public function add(State $state)
    {
        $this->states[Utils::arrayToStringSorted($state->getAnalysisElements())] = $state;
    }

    /**
     * @param array $elements
     *
     * @return State|null
     */
    public function findStateWithElements(array $elements)
    {
        $key = Utils::arrayToStringSorted($elements);

        return isset($this->states[$key]) ? $this->states[$key] : null;
    }

    /**
     * @return \ArrayIterator|State[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->states);
    }
}