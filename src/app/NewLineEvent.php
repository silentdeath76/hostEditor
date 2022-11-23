<?php


namespace app;


class NewLineEvent
{
    public function save($hostFile, $server, $host, $comment, $container, $eventController)
    {
        $line = $hostFile->addLine($server->text, $host->text, $comment->text);
        $line->active = true;
        $eventController->makeLine($line, $container);
        $hostFile->save();
    }
}