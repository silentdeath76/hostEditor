<?php


namespace app\ui\switchButton;


use php\gui\animation\UXPathAnimation;
use php\gui\layout\UXAnchorPane;
use php\io\ResourceStream;
use php\gui\shape\{UXCircle, UXRectangle};
use php\gui\UXToggleButton;
use php\lang\IllegalArgumentException;

class UXSwitchButton extends UXToggleButton
{
    private $_styleClassName = 'switch-button';
    private $lessSize = 6;

    /**
     * @var UXPathAnimation
     */
    private $animation;

    /**
     * UXSwitchButton constructor.
     * @throws IllegalArgumentException
     */
    public function __construct()
    {
        parent::__construct();

        $this->classes->add($this->_styleClassName);

        $this->stylesheets->add((new ResourceStream("/app/ui/switchButton/style.css"))->toExternalForm());

        $this->graphic = new UXAnchorPane();
        $this->graphic->add($bg = new UXRectangle());
        $bg->classes->add("bg-rect");
        $bg->arcHeight = 25;
        $bg->arcWidth = 25;

        $this->graphic->add($switcher = new UXCircle(0));
        $this->animation = new UXPathAnimation(100, $switcher);
        $switcher->classes->add("switcher");


        // что бы UXAnchorPane принял размеры в зависимости от того какой рзамер UXSwitchButton
        // и занял нужную позицию в соотествии стем отмечен UXSwitchButton или нет
        $this->observer("width")->addOnceListener(function ($o, $n) use ($bg) {
            $bg->width = $n;
        });

        $this->observer("height")->addOnceListener(function ($o, $n) use ($bg, $switcher) {
            $bg->height = $n;

            $switcher->width = $n - $this->lessSize;
            $switcher->height = $n - $this->lessSize;

            $switcher->x = $this->lessSize / 2;
            $switcher->y = $this->lessSize / 2;

            if ($this->selected) {
                $switcher->x = $bg->width - $switcher->width - $this->lessSize / 2;
            }
        });

        $this->on("click", function () use ($bg, $switcher) {
            if ($this->selected) {
                $start = $this->lessSize / 2;
                $end = ($bg->width - $switcher->width) - $this->lessSize;
            } else {
                if ($switcher->x == 29) {
                    $switcher->x = 3;
                }
                $start = ($bg->width - $switcher->width) - $this->lessSize;
                $end = $this->lessSize / 2;
            }

            $this->animation
                ->addMoveTo($start, 0)
                ->addLineTo($end, 0)
                ->playFromStart();

            $this->animation->clearPath();
        }, "___");

    }

    /**
     * Change duration animation
     * @param $duration
     */
    public function setAnimateDuration($duration)
    {
        $this->animation->duration = $duration;
    }
}