<?php


namespace app;


use app\host\{AbstractLine, HostPair};
use app\ui\Host;
use app\ui\switchButton\UXSwitchButton;
use php\gui\{layout\UXHBox, layout\UXScrollPane, UXLabel, UXTextField};
use php\time\Timer;

class LineNodeEvent
{
    /**
     * @var UXTextField
     */
    private $edit;

    /**
     * @var UXScrollPane
     */
    private $container;

    /**
     * @var Host
     */
    private $host;

    /**
     * @var int
     */
    private $scrollPos;


    public function __construct(Host $host)
    {
        $this->edit = new UXTextField();
        $this->host = $host;
    }


    public function serverEvent(UXHBox $lineBox, UXLabel $server, HostPair $line)
    {
        $this->recoveryNodeElement($this->edit);

        $this->edit->text = $server->text;
        $this->edit->width = $server->minWidth;
        $this->edit->data("node", $server);
        $this->edit->data("lineBox", $lineBox);

        $this->edit->on("keyDown", function ($e) use ($lineBox, $server, $line) {
            $this->keyPushAction($e, $lineBox, $server, $line, "Server");
        });

        $lineBox->children->replace($server, $this->edit);
        $this->edit->requestFocus();
    }

    public function hostEvent(UXHBox $lineBox, UXLabel $host, HostPair $line)
    {
        $this->recoveryNodeElement($this->edit);

        $this->edit->data("node", $host);
        $this->edit->data("lineBox", $lineBox);

        $this->edit->text = $host->text;
        $this->edit->width = $host->minWidth;
        $this->edit->on("keyDown", function ($e) use ($lineBox, $host, $line) {
            $this->keyPushAction($e, $lineBox, $host, $line, "Host");
        });

        $lineBox->children->replace($host, $this->edit);
        $this->edit->requestFocus();
    }

    public function commentEvent(UXHBox $lineBox, UXLabel $comment, HostPair $line)
    {
        $this->recoveryNodeElement($this->edit);

        $this->edit->data("node", $comment);
        $this->edit->data("lineBox", $lineBox);

        $this->edit->text = $comment->text;
        $this->edit->width = $comment->minWidth;
        $this->edit->on("keyDown", function ($e) use ($lineBox, $comment, $line) {
            $this->keyPushAction($e, $lineBox, $comment, $line, "Comment");
        });

        $lineBox->children->replace($comment, $this->edit);
        $this->edit->requestFocus();
    }

    /**
     * @param $e
     * @param UXHBox $container
     * @param UXLabel $node
     * @param HostPair $line
     * @param null $method
     */
    private function keyPushAction($e, UXHBox $container, UXLabel $node, HostPair $line, $method = null): void
    {
        switch ($e->codeName) {
            case 'Esc':
                break;
            case 'Enter':
                $line->{"set" . $method}($e->sender->text);
                $this->host->save();
                break;

            default:
                return;
        }

        $container->children->replace($e->sender, $e->sender->data("node"));

        // ебанутейщий костыль хер знает как фиксить
        // когда делаем обратную замену елементов то скрол улетает вверх
        Timer::after(10, function () use ($e) {
            if ($this->container->scrollY == $this->scrollPos) {
                Timer::after(10, function () use ($e) {
                    $this->container->scrollToNode($e->sender->data("node"));
                    $this->container->scrollY = $this->scrollPos;

                    $e->sender->data("node", null);
                });
            } else {
                $this->container->scrollToNode($e->sender->data("node"));
                $this->container->scrollY = $this->scrollPos;

                $e->sender->data("node", null);
            }
        });

        $node->text = $line->{"get" . $method}();
    }


    /**
     * @param AbstractLine $line
     * @param UXScrollPane $container
     */
    function makeLine(AbstractLine $line, UXScrollPane $container): void
    {
        $this->container = $container;
        $height = 27;

        if ($line instanceof HostPair) {
            $lineBox = new UXHBox();
            $lineBox->spacing = 5;
            $lineBox->padding = 5;


            $lineBox->add($server = $this->makeLabel($line->getServer(), 120, 25, function () use ($lineBox, &$server, $line) {
                $this->serverEvent($lineBox, $server, $line);
            }));
            $server->font->bold = true;

            $lineBox->add($host = $this->makeLabel($line->getHost(), 200, $height, function () use ($lineBox, &$host, $line) {
                $this->hostEvent($lineBox, $host, $line);
            }));

            $lineBox->add($comment = $this->makeLabel($line->getComment(), 200, $height, function () use ($lineBox, &$comment, $line) {
                $this->commentEvent($lineBox, $comment, $line);
            }));
            $comment->font->italic = true;


            $lineBox->add($sw = new UXSwitchButton());
            $sw->selected = $line->active;
            $sw->width = 45;
            $sw->height = 20;
            $sw->on("click", function () use ($sw, $line) {
                $line->active = $sw->selected;
                $this->host->save();
            });


            $container->content->add($lineBox);
        }
    }


    /**
     * @param UXTextField $edit
     */
    public function recoveryNodeElement(UXTextField $edit): void
    {
        $this->scrollPos = $this->container->scrollY;
        if ($edit->data("node") !== null) {
            $edit->data("lineBox")->children->replace($edit, $edit->data("node"));
        }
    }


    /**
     * @param string $text
     * @param int $width
     * @param int $height
     * @param callable $click
     * @return UXLabel
     */
    private function makeLabel(string $text, int $width, int $height, callable $click): UXLabel
    {
        $label = new UXLabel($text);
        $label->minWidth = $width;
        $label->height = $height;
        $label->on("click", $click);

        return $label;
    }
}