<?php


namespace MysqlBinlog;


class ByteStreamReader
{
    private $data;
    private $length;
    private $offset = 0;

    public function __construct(&$data)
    {
        $this->data = &$data;
        $this->length = strlen($data);
    }

    public function readUint64()
    {
        $result = unpack('P', $this->data, $this->offset)[1];
        $this->offset += 8;
        return $result;
    }

    public function readUint48()
    {
        $low = $this->readUint32();
        $high = $this->readUint16();
        return ($high * (2 ^ 32)) + $low;
    }

    public function readUint32()
    {
        $result = unpack('V', $this->data, $this->offset)[1];
        $this->offset += 4;
        return $result;
    }

    public function readUint16()
    {
        $result = unpack('v', $this->data, $this->offset)[1];
        $this->offset += 2;
        return $result;
    }

    public function readUint8()
    {
        $result = unpack('C', $this->data, $this->offset)[1];
        $this->offset += 1;
        return $result;
    }

    public function readNulPaddedString($length)
    {
        $result = unpack("Z{$length}", $this->data, $this->offset)[1];
        $this->offset += $length;
        return $result;
    }

    public function readData($length)
    {
        $result = substr($this->data, $this->offset, $length);
        $this->offset += $length;
        return $result;
    }

    public function readRemain()
    {
        $result = substr($this->data, $this->offset);
        $this->offset += $this->length - $this->offset;

        return $result;
    }
}