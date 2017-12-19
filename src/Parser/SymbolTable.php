<?php

namespace CompilerDesign\Parser;

class SymbolTable
{
    private $codeCounter;
    private $map;
    private $reversedMap;

    public function __construct()
    {
        $this->codeCounter = -1;
        $this->map         = array();
    }

    public function put($value)
    {
        if (isset($this->map[$value])) {
            return $this->map[$value];
        }

        $this->codeCounter++;
        $this->map[$value]                     = $this->codeCounter;
        $this->reversedMap[$this->codeCounter] = $value;

        return $this->codeCounter;
    }

    public function getCodeCounter($value)
    {
        return $this->map[$value];
    }

    public function getValue($code)
    {
        return $this->reversedMap[$code];
    }

    public function toArray()
    {
        return $this->reversedMap;
    }
}