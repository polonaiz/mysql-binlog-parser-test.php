<?php

namespace MysqlBinlog;

use PHPUnit\Framework\TestCase;

class ByteStreamReaderTest extends TestCase
{
    public function test()
    {
        $data = pack('V', PHP_INT_MAX);
        var_dump(bin2hex($data));
        $value = decbin(1000);
        var_dump(bin2hex($value));
        var_dump(bindec($value));
//        debug_zval_dump($value);
    }
}
