<?php


namespace CompilerDesign;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class State
{
    const ACTION_ACCEPT = "acc";
    const ACTION_SHIFT  = "shift";
    const ACTION_REDUCE = "reduce";

    private $action;
    private $goto;
    private $productions;
    private $reducePosition;
}