<?php


namespace app\ui;


use app\host\{AbstractLine, CommentLine, HostPair};
use php\lang\IllegalArgumentException;
use php\gui\{UXApplication, UXForm, UXLabel};
use php\io\{File, FileStream, IOException, Stream};
use php\time\Timer;
use php\util\Scanner;

class Host
{
    /**
     * @var Stream
     */
    private $stream;

    /**
     * @var UXForm
     */
    private $form;

    /**
     * @var CommentLine
     */
    private $commentLine;

    /**
     * @var HostPair
     */
    private $hostPairLine;

    private $lines = [];


    public function __construct(Stream $stream, UXForm $form = null)
    {
        $this->commentLine = new CommentLine();
        $this->hostPairLine = new HostPair();
        $this->stream = $stream;
        $this->form = $form;

        echo 'Reading: ' . $stream->getPath() . "\r\n";

        try {
            $scanner = new Scanner($stream);

            while ($scanner->hasNextLine()) {
                $line = trim($scanner->nextLine());

                if ($this->hostPairLine->validate($line)) {
                    $this->lines[] = $obj = new HostPair();
                    $obj->parse($line);
                } else if ($this->commentLine->validate($line)) {
                    $this->lines[] = $obj = new CommentLine();
                    $obj->setLine($line);
                } else {
                    if ($line == null) continue;

                    echo "unknown type line\r\n";
                }
            }
        } catch (IllegalArgumentException $e) {
            $this->errorMessage($e->getMessage());
        } finally {
            $stream->close();
        }
    }


    /**
     * @return AbstractLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }


    public function addLine($server, $host, $comment): AbstractLine
    {
        $this->lines[] = $obj = new HostPair();
        $obj->setServer($server);
        $obj->setHost($host);
        $obj->setComment($comment);

        return $obj;
    }


    public function save()
    {
        echo "save changes...\r\n";


        try {
            $outputStream = new FileStream($this->stream->getPath(), "w");

            foreach ($this->getLines() as $line) {
                $outputStream->write($line->getLine() . "\r\n");
            }

            $outputStream->close();
        } catch (IOException $e) {
            switch ($e->getMessage()) {
                case 'Отказано в доступе':
                    $this->errorMessage("Can't write to file, please run app from administrator.");
                    break;

                case 'File \'' . $this->stream->getPath() . '\' not found':
                    if (File::of($this->stream->getPath())->exists()) {
                        $this->errorMessage("Can't write to file, please run app from administrator.");
                    } else {
                        $this->errorMessage('File \'' . $this->stream->getPath() . '\' not found');
                    }
                    break;

                default:
                    $this->errorMessage("Unknown error: " . $e->getMessage());
            }
        }
    }


    private function errorMessage($message)
    {
        if (!($this->form instanceof UXForm)) {
            echo $message . "\r\n";
            return;
        }

        $this->form->add($label = new UXLabel($message));
        $label->style = '-fx-border-width: 0 0 2 0; -fx-border-color: #c22929; -fx-background-color: #cb2e2e; -fx-text-fill: white; -fx-padding: 10;';
        $label->rightAnchor = $label->leftAnchor = 0;
        $label->alignment = "CENTER_LEFT";
        $label->toFront();

        Timer::after(3000, function () use ($label) {
            UXApplication::runLater(function () use ($label) {
                $label->free();
            });
        });
    }
}