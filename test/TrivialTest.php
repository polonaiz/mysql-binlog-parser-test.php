<?php

use PHPUnit\Framework\TestCase;

class TrivialTest extends TestCase
{
    public function test()
    {
        $file = fopen('/data/mysql-bin.144062', 'r');
        $buffer = fread($file, 512);
        $pos = 0;
        $bufferPos = 0;
//        echo bin2hex($buffer) . PHP_EOL . PHP_EOL;

        //
        $fileHeaderSize = 4;
        $fileHeaderData = substr($buffer, $pos - $bufferPos, $fileHeaderSize);
        $this->assertEquals("\xfebin", $fileHeaderData);
        echo json_encode(['pos' => $pos, 'type' => 'fileHeader', 'data' => bin2hex($fileHeaderData)]) . PHP_EOL;
        $pos += $fileHeaderSize;

        $eventHeaderSize = 19;
        $eventHeaderData = substr($buffer, $pos - $bufferPos, $eventHeaderSize);
        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $eventHeaderData);
        echo json_encode(['pos' => $pos, 'type'=> 'eventHeader', 'unpacked' => $unpacked, 'data' => bin2hex($eventHeaderData)]) . PHP_EOL;
        $pos += $eventHeaderSize;
        $eventBodySize = $unpacked['eventLength'] - $eventHeaderSize;
        $eventBodyData = substr($buffer, $pos - $bufferPos, $eventBodySize);
        echo bin2hex($eventBodyData) . PHP_EOL;
        $pos += $eventBodySize;

        $eventHeaderSize = 19;
        $eventHeaderData = substr($buffer, $pos - $bufferPos, $eventHeaderSize);
        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $eventHeaderData);
        echo json_encode(['pos' => $pos, 'type'=> 'eventHeader', 'unpacked' => $unpacked, 'data' => bin2hex($eventHeaderData)]) . PHP_EOL;
        $pos += $eventHeaderSize;
        $eventBodySize = $unpacked['eventLength'] - $eventHeaderSize;
        $eventBodyData = substr($buffer, $pos - $bufferPos, $eventBodySize);
        echo bin2hex($eventBodyData) . PHP_EOL;
        $pos += $eventBodySize;

        $eventHeaderSize = 19;
        $eventHeaderData = substr($buffer, $pos - $bufferPos, $eventHeaderSize);
        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $eventHeaderData);
        echo json_encode(['pos' => $pos, 'type'=> 'eventHeader', 'unpacked' => $unpacked, 'data' => bin2hex($eventHeaderData)]) . PHP_EOL;;
        $pos += $eventHeaderSize;
        $eventBodySize = $unpacked['eventLength'] - $eventHeaderSize;
        $eventBodyData = substr($buffer, $pos - $bufferPos, $eventBodySize);
        echo bin2hex($eventBodyData) . PHP_EOL;
        $pos += $eventBodySize;

        $eventHeaderSize = 19;
        $eventHeaderData = substr($buffer, $pos - $bufferPos, $eventHeaderSize);
        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $eventHeaderData);
        echo json_encode(['pos' => $pos, 'type'=> 'eventHeader', 'unpacked' => $unpacked, 'data' => bin2hex($eventHeaderData)]) . PHP_EOL;;
        $pos += $eventHeaderSize;
        $eventBodySize = $unpacked['eventLength'] - $eventHeaderSize;
        $eventBodyData = substr($buffer, $pos - $bufferPos, $eventBodySize);
        echo bin2hex($eventBodyData) . PHP_EOL;
        $pos += $eventBodySize;

        $eventHeaderSize = 19;
        $eventHeaderData = substr($buffer, $pos - $bufferPos, $eventHeaderSize);
        $unpacked = unpack('Vtimestamp/Ctype/VserverId/VeventLength/VnextPosition/vflags', $eventHeaderData);
        echo json_encode(['pos' => $pos, 'type'=> 'eventHeader', 'unpacked' => $unpacked, 'data' => bin2hex($eventHeaderData)]) . PHP_EOL;;
        $pos += $eventHeaderSize;
        $eventBodySize = $unpacked['eventLength'] - $eventHeaderSize;
        $eventBodyData = substr($buffer, $pos - $bufferPos, $eventBodySize);
        echo bin2hex($eventBodyData) . PHP_EOL;
        $pos += $eventBodySize;

        $this->assertTrue(true);
    }
}
