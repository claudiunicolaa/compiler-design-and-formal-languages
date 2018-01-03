<?php


namespace CompilerDesign\Analyzer;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class Utils
{
    public static function arrayToString(array $elements)
    {
        $elementsString = implode(', ', $elements);

        return '{'.$elementsString.'}';
    }
}