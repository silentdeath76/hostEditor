<?php

use app\LineNodeEvent;
use app\ui\{Host, NewLinePanel};
use php\gui\{
    layout\UXScrollPane,
    layout\UXVBox,
    UXAlert,
    UXApplication,
    UXFlatButton,
    UXForm
};
use php\io\File;
use php\io\FileStream;
use php\lang\System;
use php\lib\fs;
use php\util\Configuration;


UXApplication::launch(function (UXForm $form) {
    $defaultHostPath = System::getEnv()["windir"] . '\System32\drivers\etc\hosts';
    $langPath = './langs';

    try {
        $hostFile = new Host(FileStream::of($defaultHostPath), $form);
    } catch (Exception $ex) {
        $alert = new UXAlert("INFORMATION");
        $alert->contentText = $ex->getMessage();
        $alert->showAndWait();

        exit(-1);
    }

    $lang = new Configuration();
    $lang->set('ru.addHost', 'Добавить запись');
    $lang->set('ru.save', 'Сохранить');
    $lang->set('ru.cancel', 'Отменить');
    $lang->set('ru.server', 'Сервер');
    $lang->set('ru.host', 'Хост');
    $lang->set('ru.comment', 'Комментарий');
    $lang->set('ru.addNewLine', 'Добавить новую запись');

    $form->lang = $lang;

    $eventController = new LineNodeEvent($hostFile);

    $form->title = "Host editor";
    $form->minWidth = $form->maxWidth = 630;
    $form->minHeight = $form->maxHeight = 450;
    $form->resizable = false;

    $form->addStylesheet('/style/base.css');

    $form->add($container = new UXScrollPane(new UXVBox()));

    $newLineController = new NewLinePanel($form, $hostFile, $container, $eventController);

    $form->add($addButton = new UXFlatButton($form->lang->get("ru.addHost")));
    $addButton->bottomAnchor = 5;
    $addButton->rightAnchor = 10;
    $addButton->classes->add("add-button");
    $addButton->on("click", [$newLineController, 'show']);
    $addButton->toBack();

    $container->leftAnchor = $container->rightAnchor = $container->topAnchor = 0;
    $container->bottomAnchor = 40;

    foreach ($hostFile->getLines() as $line) {
        $eventController->makeLine($line, $container);
    }

    $form->show();
});