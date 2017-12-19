<?php


namespace CompilerDesign\Parser;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class Position
{
    private $line;
    private $col;

    public function __construct(int $line, int $col)
    {
        $this->line = $line;
        $this->col  = $col;
    }

    public function __toString()
    {
        return "line: $this->line, col: $this->col";
    }
}