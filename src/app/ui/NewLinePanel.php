<?php


namespace app\ui;


use app\{LineNodeEvent, NewLineEvent};
use php\gui\{layout\UXHBox,
    layout\UXPane,
    layout\UXPanel,
    text\UXFont,
    UXFlatButton,
    UXForm,
    UXLabel,
    UXNode,
    UXTextField};

class NewLinePanel
{
    /**
     * @var UXPane
     */
    private $panel;

    /**
     * @var NewLineEvent
     */
    private $events;
    /**
     * @var UXPanel
     */
    private $overlay;

    public function __construct(UXForm $form, Host $hostFile, $container, LineNodeEvent $eventController)
    {
        $this->events = new NewLineEvent();

        $this->panel = new UXPanel();
        $this->panel->rightAnchor = $this->panel->leftAnchor = 0;
        $this->panel->topAnchor = $this->panel->bottomAnchor = 40;
        $this->panel->backgroundColor = "white";
        $this->panel->padding = 10;

        $this->panel->add($label = new UXLabel($form->lang->get("ru.addNewLine")));
        $label->font = UXFont::of('Segoe UI Light', 31);
        $label->textColor = "#666666";
        $label->alignment = 'CENTER';
        $label->x = 10;
        $label->leftAnchor = $label->rightAnchor = 50;

        $server = $this->makeServerUI($form);
        $host = $this->makeHostUI($form);
        $comment = $this->makeCommentUI($form);

        $buttonContainer = new UXHBox();
        $buttonContainer->add($save = new UXFlatButton($form->lang->get("ru.save")));
        $save->classes->add('add-button');
        $save->minWidth = 70;
        $save->alignment = 'CENTER';
        $save->on("click", function () use ($server, $hostFile, $host, $comment, $container, $eventController) {
            $this->events->save($hostFile, $server, $host, $comment, $container, $eventController);
            $this->hide();
        });

        $buttonContainer->add($cancel = new UXFlatButton($form->lang->get("ru.cancel")));
        $cancel->classes->add('add-button');
        $cancel->minWidth = 70;
        $cancel->alignment = 'CENTER';
        $cancel->on("click", function () {
            $this->hide();
        });

        $buttonContainer->spacing = 5;
        $buttonContainer->bottomAnchor = 20;
        $buttonContainer->rightAnchor = 50;

        $this->panel->add($buttonContainer);
        $this->makeOverlay($form);
        $form->add($this->panel);
        $this->hide();
    }

    private function makeOverlay(UXForm $form)
    {
        $this->overlay = new UXPanel();
        $this->overlay->backgroundColor = '#0000000f';
        $this->overlay->anchors = ["left" => 0, "top" => 0, "right" => 0, "bottom" => 0];
        $form->add($this->overlay);
    }

    public function show()
    {
        $this->overlay->show();
        $this->panel->show();
    }

    public function hide()
    {
        $this->overlay->hide();
        $this->panel->hide();
    }

    /**
     * @param UXForm $form
     * @return UXTextField
     */
    private function makeServerUI(UXForm $form): UXTextField
    {
        return $this->makeUIPair($form->lang->get("ru.server"), '127.0.0.1', 55);
    }

    /**
     * @param UXForm $form
     * @return UXTextField
     */
    private function makeHostUI(UXForm $form): UXTextField
    {
        return $this->makeUIPair($form->lang->get("ru.host"), 'localhost', 120);
    }

    /**
     * @param UXForm $form
     * @return UXTextField
     */
    private function makeCommentUI(UXForm $form): UXTextField
    {
        return $this->makeUIPair($form->lang->get("ru.comment"), ' commentary', 195);
    }

    /**
     * @param $caption
     * @param $prompt
     * @param $posY
     * @return UXTextField
     */
    private function makeUIPair($caption, $prompt, $posY): UXTextField
    {
        $label = new UXLabel($caption);
        $label->y = $posY;
        $label->font->bold = true;
        $label->leftAnchor = $label->rightAnchor = 55;

        $field = new UXTextField();
        $field->promptText = $prompt;
        $field->y = $posY + 20;
        $field->leftAnchor = $field->rightAnchor = 50;

        $this->panel->add($label);
        $this->panel->add($field);

        return $field;
    }
}