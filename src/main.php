<?php

use MysqlBinlog\FileReader;

require_once __DIR__ . "/../vendor/autoload.php";

$reader = new FileReader([
    'filename' => '/data/mysql-bin.144062'
]);

$eventNum = 0;
$eventPrev = null;
while (true)
{
    $event = $reader->readEvent();
    if ($event === false)
    {
        break;
    }
    $eventNum++;
    $eventPrev = $event;

    echo json_encode([
            'eventNum' => $eventNum,
            'event' => $event
        ]) . PHP_EOL;

}
