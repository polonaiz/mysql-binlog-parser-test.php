<?php

namespace MysqlBinlog;

use PHPUnit\Framework\TestCase;

class FileReaderTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function test()
    {
        $reader = new FileReader([
            'filename' => '/data/mysql-bin.144062'
        ]);
        $eventCount = 0;
        try
        {
            while (true)
            {
                $event = $reader->readEvent();
                if ($event === false)
                {
                    break;
                }
                $eventCount++;

//                echo json_encode([
//                        'eventCount' => $eventCount,
//                        'event' => $event
//                    ]) . PHP_EOL;
            }
        }
        catch (\Exception $e)
        {
            echo $e->getMessage() . PHP_EOL;
        }
        echo json_encode([
                'eventCount' => $eventCount,
            ]) . PHP_EOL;
        $this->assertTrue(true);
    }
}
