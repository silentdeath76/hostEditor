<?php


namespace app\host;


abstract class AbstractLine
{
    protected $line;

    abstract public function validate($line);

    abstract public function getLine () : string;
}