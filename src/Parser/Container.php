<?php

namespace CompilerDesign\Parser;

class Container
{
    private $code;
    private $map;
    private $reversedMap;

    public function __construct()
    {
        $this->code = -1;
        $this->map  = array();
    }

    public function put($value)
    {
        if (isset($this->map[$value])) {
            return $this->map[$value];
        }

        $this->code++;
        $this->map[$value]              = $this->code;
        $this->reversedMap[$this->code] = $value;

        return $this->code;
    }

    public function getCode($value)
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