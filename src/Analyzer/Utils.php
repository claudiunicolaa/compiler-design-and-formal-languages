<?php


namespace CompilerDesign\Analyzer;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class Utils
{
    public static function arrayToStringSorted(array $elements)
    {
        $elements = array_map('strval', $elements);
        sort($elements);

        $elementsString = implode(', ', $elements);

        return '{'.$elementsString.'}';
    }
}