<?php


namespace app\host;


use php\util\Regex;

class HostPair extends AbstractLine
{
    const IP_REGEX = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s*([\w\.-]+)($|\s*[#\w+]+.*?$)';


    /**
     * @var string
     */
    private $server;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $comment;
    /**
     * @var bool
     */
    public $active;


    public function validate($line = null): bool
    {
        $regex = Regex::of(self::IP_REGEX)->with($line);
        return $regex->find();
    }


    public function parse($line)
    {
        $regex = Regex::of(self::IP_REGEX)->with($line);

        $data = $regex->all();

        $this->server = trim($data[0][1]);
        $this->host = trim($data[0][2]);
        $this->comment = trim($data[0][3]);
        $this->active = !(substr(trim($line), 0, 1) === '#');
    }


    public function getLine(): string
    {
        $line = $this->active ? "" : "# ";
        $line .= $this->getServer() . " ";
        $line .= $this->getHost();
        $line .= " " . $this->getComment();

        return trim($line);
    }

    public function getServer(): string
    {
        return $this->server;
    }


    public function getHost(): string
    {
        return $this->host;
    }


    public function getComment(): string
    {
        if (strlen($this->comment) > 0) {
            if (substr(trim($this->comment), 0, 1) !== '#') {
                $this->comment = ' # ' . $this->comment;
            }
        }

        return $this->comment;
    }

    public function setServer($server)
    {
        $this->server = trim($server);
    }

    public function setHost($host)
    {
        $this->host = trim($host);
    }

    public function setComment($comment)
    {
        $this->comment = trim($comment);
    }
}