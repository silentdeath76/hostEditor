<?php


use app\ui\Host;
use app\host\HostPair;
use tester\{Assert, TestCase};
use php\io\MemoryStream;

class HostTest extends TestCase
{
    private $data;

    /**
     * HostTest constructor.
     */
    public function __construct()
    {
        $this->data = new MemoryStream();
        $this->data->write("# comment block\r\n");
        $this->data->write("# 127.0.0.1 localhost\r\n");
        $this->data->write("# 127.0.0.1 localhost # commented block\r\n");
        $this->data->write("127.0.0.1 localhost # commented block\r\n");
    }

    public function testHostData()
    {
        $this->data->seek(0);
        $host = new Host($this->data);

        foreach ($host->getLines() as $key => $line) {
            if ($line instanceof HostPair) {
                switch ($key) {
                    case 1:
                        Assert::isEqual($line->active, false, "line is active");
                        break;
                    case 2:
                        Assert::isEqual($line->getComment(), "# commented block", 'wrong comment');
                        break;

                    case 3:
                        Assert::isEqual($line->active, true, "line is not active");
                        Assert::isEqual($line->getHost(), "localhost", "wrong server");
                        Assert::isEqual($line->getServer(), "127.0.0.1", "wrong host");
                        Assert::isEqual($line->getComment(), "# commented block", "wrong comment");
                }
            }
        }
    }

    public function testUpdateData()
    {
        $this->data->seek(0);
        $host = new Host($this->data);

        foreach ($host->getLines() as $key => $line) {
            if ($key == 3) {
                $line->setHost("vk.com");
                $line->setServer("127.0.0.2");
                $line->setComment("changed comment");
                $line->active = false;


                Assert::isEqual($line->getHost(), "vk.com", "dont updated host");
                Assert::isEqual($line->getServer(), "127.0.0.2", "dont updated server");
                Assert::isEqual($line->getComment(), " # changed comment", "dont updated comment");
                Assert::isEqual($line->active, false, "dont changed active");
            }
        }
    }
}