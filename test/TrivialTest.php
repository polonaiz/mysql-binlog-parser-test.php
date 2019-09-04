<?php

use PHPUnit\Framework\TestCase;

class TrivialTest extends TestCase
{
    private const READ_BLOCK_SIZE = 8;

    /**
     * @param $file resource
     * @param $readPos integer
     * @param $windowBegin integer
     * @param $windowData string
     * @param $windowSize integer
     * @param $needSize integer
     * @throws Exception
     */
    private static function checkAndFeedWindowData($file, &$readPos, &$windowBegin, &$windowData, &$windowSize, $needSize)
    {
        echo json_encode([
                'readPos' => $readPos,
                'windowBegin' => $windowBegin,
                'windowSize' => $windowSize,
                'needSize' => $needSize,
            ]) . PHP_EOL;

        while ($windowBegin + $windowSize - $readPos < $needSize)
        {
            $readData = fread($file, self::READ_BLOCK_SIZE);
            $readSize = strlen($readData);
            if ($readData === false || $readSize === 0)
            {
                throw new Exception();
            }
            $windowData .= $readData;
            $windowSize += $readSize;
        }
    }

    private static function checkAndTrimWindowData()
    {
        // TODO
    }

    /**
     * @param $file resource
     * @param &$readPos integer
     * @param &$windowBegin integer
     * @param &$windowData string
     * @param &$windowSize integer
     * @return array
     * @throws Exception
     */
    private static function readFileHeader($file, &$readPos, &$windowBegin, &$windowData, &$windowSize)
    {
        $needSize = $fileHeaderSize = 4;

        self::checkAndFeedWindowData($file, $readPos, $windowBegin, $windowData, $windowSize, $needSize);

        $fileHeaderData = substr($windowData, $readPos - $windowBegin, $fileHeaderSize);
        $readPos += $fileHeaderSize;

        return ['data' => $fileHeaderData];
    }

    /**
     * @param $file resource
     * @param &$readPos integer
     * @param &$windowBegin integer
     * @param &$windowData string
     * @param &$windowSize integer
     * @return array|false
     * @throws Exception
     */
    private function readEventHeader($file, &$readPos, &$windowBegin, &$windowData, &$windowSize)
    {
        $needSize = $eventHeaderSize = 19;

        self::checkAndFeedWindowData($file, $readPos, $windowBegin, $windowData, $windowSize, $needSize);

        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $windowData, $readPos - $windowBegin);
        $readPos += $eventHeaderSize;

        return $unpacked;
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $file = fopen('/data/mysql-bin.144062', 'rb');
        $readPos = 0;
        $windowBegin = 0;
        $windowData = '';
        $windowSize = 0;
        echo json_encode(['type' => 'begin']) . PHP_EOL;
        echo json_encode(['readPos' => $readPos]) . PHP_EOL;

        //
        $fileHeader = $this->readFileHeader($file, $readPos, $windowBegin, $windowData, $windowSize);
        $this->assertEquals("\xfebin", $fileHeader['data']);
        echo json_encode(['type' => 'fileHeader', 'fileHeader' => $fileHeader]) . PHP_EOL;
        echo json_encode(['readPos' => $readPos]) . PHP_EOL;

        //
        for($count = 0; $count < 100; $count++)
        {
            $eventHeader = $this->readEventHeader($file, $readPos, $windowBegin, $windowData, $windowSize);
            echo json_encode(['type' => 'eventHeader', 'eventHeader' => $eventHeader]) . PHP_EOL;
            echo json_encode(['readPos' => $readPos]) . PHP_EOL;
            $readPos += $eventHeader['eventLength'] - 19;
        }

        //
        fclose($file);
    }
}
