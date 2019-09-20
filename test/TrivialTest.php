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
//        echo json_encode([
//                'loc' => 'checkAndFeedWindowData',
//                'readPos' => $readPos,
//                'windowBegin' => $windowBegin,
//                'windowSize' => $windowSize,
//                'needSize' => $needSize,
//            ]) . PHP_EOL;

        while ($windowBegin + $windowSize - $readPos < $needSize)
        {
            $readData = fread($file, self::READ_BLOCK_SIZE);
            if ($readData === false)
            {
                throw new Exception("readData is false");
            }
            $readSize = strlen($readData);
            if ($readSize === 0)
            {
                throw new Exception("readSize is zero");
            }
            $windowData .= $readData;
            $windowSize += $readSize;
        }
    }

    private static function checkAndDiscardWindowData($file, &$readPos, &$windowBegin, &$windowData, &$windowSize, $needSize)
    {
//        echo json_encode([
//                'loc' => 'checkAndDiscardWindowData',
//                'readPos' => $readPos,
//                'windowBegin' => $windowBegin,
//                'windowSize' => $windowSize,
//                'needSize' => $needSize,
//            ]) . PHP_EOL;

        while ($windowBegin + self::READ_BLOCK_SIZE < $readPos)
        {
            $windowData = substr($windowData, self::READ_BLOCK_SIZE);
            $windowBegin += self::READ_BLOCK_SIZE;
            $windowSize -= self::READ_BLOCK_SIZE;

//            echo json_encode([
//                    'loc' => 'checkAndDiscardWindowData',
//                    'readPos' => $readPos,
//                    'windowBegin' => $windowBegin,
//                    'windowSize' => $windowSize,
//                    'needSize' => $needSize,
//                ]) . PHP_EOL;
        }

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

        self::checkAndDiscardWindowData($file, $readPos, $windowBegin, $windowData, $windowSize, $needSize);

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

        self::checkAndDiscardWindowData($file, $readPos, $windowBegin, $windowData, $windowSize, $needSize);

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
        echo json_encode(['readPos' => $readPos]) . PHP_EOL. PHP_EOL;

        //
        $fileHeader = $this->readFileHeader($file, $readPos, $windowBegin, $windowData, $windowSize);
        $this->assertEquals("\xfebin", $fileHeader['data']);
        echo json_encode(['type' => 'fileHeader', 'fileHeader' => bin2hex($fileHeader['data'])]) . PHP_EOL;
        echo json_encode(['readPos' => $readPos]) . PHP_EOL. PHP_EOL;

        //
//        for($count = 0; $count < 100; $count++)
            for(;;)
        {
            $eventHeader = $this->readEventHeader($file, $readPos, $windowBegin, $windowData, $windowSize);
//            echo json_encode(['type' => 'eventHeader', 'eventHeader' => $eventHeader]) . PHP_EOL;
//            echo json_encode(['readPos' => $readPos]) . PHP_EOL. PHP_EOL;
            $readPos += $eventHeader['eventLength'] - 19;
        }

        //
        fclose($file);
    }
}
