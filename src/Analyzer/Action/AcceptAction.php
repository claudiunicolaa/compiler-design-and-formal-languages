<?php


namespace CompilerDesign\Analyzer\Action;

use CompilerDesign\Analyzer\Action;
use CompilerDesign\Analyzer\AnalysisContext;

/**
 * @author Marius Adam <marius.adam134@gmail.com>
 */
class AcceptAction implements Action
{
    private static $instance;

    final private function __construct()
    {
    }

    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}