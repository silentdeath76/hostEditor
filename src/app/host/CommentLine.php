<?php


namespace app\host;



class CommentLine extends AbstractLine
{

    public function validate($line = null)
    {
        return (substr($line, 0, 1) === '#');
    }

    public function setLine(string $line)
    {
        $this->line = $line;
    }

    public function getLine(): string
    {
        return $this->line;
    }
}