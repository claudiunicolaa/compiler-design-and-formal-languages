<?php


namespace CompilerDesign\Analyzer;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class Dot
{
    private static $instance;

    private function __construct()
    {
    }

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __toString()
    {
        return '.';
    }
}