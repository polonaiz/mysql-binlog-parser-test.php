<?php


namespace MysqlBinlog;


class FileReader
{
    private const EVENT_HEADER_SIZE = 19;
    private $config;
    private $file;
    private $tableMap = [];

    public function __construct($config = [])
    {
        $this->config = $config;

        $filename = $config['filename'];
        $file = fopen($filename, 'rb');

        $fileHeader = fread($file, 4);
        if (strcmp($fileHeader, "\xfebin") != 0)
        {
            throw new \Exception("not binlog file");
        }
        $this->file = $file;
    }

    /**
     * @throws \Exception
     */
    public function readEvent()
    {
        //
        $eventHeaderData = fread($this->file, self::EVENT_HEADER_SIZE);
        $eventHeaderDataSize = strlen($eventHeaderData);
        if ($eventHeaderDataSize != self::EVENT_HEADER_SIZE)
        {
            if (feof($this->file))
            {
                return false;
            }
            $pos = ftell($this->file);
            $eof = feof($this->file) ? 'true' : 'false';
            throw new \Exception("FAILURE: eventHeaderDataSize={$eventHeaderDataSize},pos={$pos},eof={$eof}");
        }
        $eventHeader = unpack('Vtimestamp/CeventType/VserverId/VeventSize/VlogPos/vflags', $eventHeaderData);

        //
        $eventSize = $eventHeader['eventSize'];
        $eventBodyData = fread($this->file, $eventSize - self::EVENT_HEADER_SIZE);

        $eventType = $eventHeader['eventType'];
        $eventTypeName = EventType::resolveEventType($eventType);

        $body = null;
        switch ($eventType)
        {
            case EventType::FORMAT_DESCRIPTION_EVENT:
                $reader = new ByteStreamReader($eventBodyData);
                $body['binlogVersion'] = $reader->readUint16();
                $body['serverVersion'] = $reader->readNulPaddedString(50);
                $body['createTimestamp'] = $reader->readUint32();
                $body['headerLength'] = $reader->readUint8();
                $body['remain'] = bin2hex($reader->readRemain());

//                $body = unpack('vbinlogVersion/Z50serverVersion/VcreateTimestamp/CheaderLength', $eventBodyData);
//                body":{"binlogVersion":4,"serverVersion":"5.5.29-log","createTimestamp":0,"headerLength":19}}}
                break;

            case EventType::QUERY_EVENT:
                // cf5aa608 slaveProxyId
                // 00000000 executionTime
                // 00 schemaLength
                // 0000 errorCode
                // 1500 statusVarsLength
                // 00 00 00 00  00 01 00 00  00 10 00 00  00 00 04 21
                // 00 21 00 21  00 - statusVarsData
                //   00 - 00000000
                //   01 - 0000001000000000
                //   04 - 210021002100
                //     2100 character_set_client
                //     2100 collation_connection
                //     2100 collation_server
                // 00 - 1
                // 424547494e - 'BEGIN'

                $reader = new ByteStreamReader($eventBodyData);
                $body['slaveProxyId'] = $reader->readUint32();
                $body['executionTime'] = $reader->readUint32();
                $body['schemaLength'] = $reader->readUint8();
                $body['errorCode'] = $reader->readUint16();
                $body['statusVarsLength'] = $reader->readUint16();
                $body['statusVarsData'] = bin2hex($reader->readData($body['statusVarsLength']));
                $body['1'] = $reader->readUint8();
                $body['query'] = $reader->readRemain();
                $body['remain'] = bin2hex($reader->readRemain());
                break;

            case EventType::TABLE_MAP_EVENT:
                $reader = new ByteStreamReader($eventBodyData);
                $body['tableId'] = $tableId = $reader->readUint48();
                $body['flags'] = bin2hex($reader->readData(2));
                $body['schemaNameLength'] = $schemaNameLength = $reader->readUint8();
                $body['schemaName'] = $reader->readData($schemaNameLength);
                $body['schemaNameNull'] = $reader->readData(1);
                $body['tableNameLength'] = $tableNameLength = $reader->readUint8();
                $body['tableName'] = $reader->readData($tableNameLength);
                $body['tableNameNull'] = $reader->readData(1);
                $body['columnCount'] = $columnCount = $reader->readUint8(); //len-enc-int
                $body['columnTypeDefData'] = bin2hex($columnTypeDefData = $reader->readData($columnCount));
                $body['columnTypeDef'] = [];
                $body['columnTypeDefResolved'] = [];
                for ($idx = 0; $idx < $columnCount; $idx++)
                {
                    $columnType = ord(substr($columnTypeDefData, $idx, 1));
                    $body['columnTypeDef'][] = $columnType;
                    $body['columnTypeDefResolved'][] = ColumnType::resolve($columnType);
                }
                $body['columnMetaLength'] = $columnMetaLength = $reader->readUint8(); //len-enc-int
                $body['columnMetaData'] = bin2hex($reader->readData($columnMetaLength));
                $body['nullBitmap'] = bin2hex($reader->readData(intval(($columnCount + 8) / 7)));
                $body['remain'] = bin2hex($reader->readRemain());
                $this->tableMap[$tableId] = $body;
                break;

            case EventType::UPDATE_ROWS_EVENTv1:
                $reader = new ByteStreamReader($eventBodyData);
                $body['tableId'] = $tableId = $reader->readUint48();
                $body['flags'] = $reader->readUint16();
                $body['columnCount'] = $columnCount = $reader->readUint8(); //len-enc-int
                $body['columnPresentBitmap1'] = bin2hex($reader->readData(intval(($columnCount + 8) / 7)));
                $body['columnPresentBitmap2'] = bin2hex($reader->readData(intval(($columnCount + 8) / 7)));
                $body['column'] = [];
//                $body['remainData'] = bin2hex($remainData = $reader->readRemain());
//                $body['remainSize'] = strlen($remainData);
                $tableMapEvent = &$this->tableMap[$tableId];
                for ($idx = 0; $idx < $tableMapEvent['columnCount']; $idx++)
                {
                    $columnType = $tableMapEvent['columnTypeDef'][$idx];
                    $columnTypeName = ColumnType::resolve($columnType);
                    switch ($columnType)
                    {
                        case ColumnType::MYSQL_TYPE_LONGLONG:
                            $value = $reader->readUint64();
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => $value,
                            ];
                            break;
                        case ColumnType::MYSQL_TYPE_TINY:
                            $value = $reader->readUint8();
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => $value,
                            ];
                            break;

                        case ColumnType::MYSQL_TYPE_DATETIME:
                            $value = $reader->readData(8);
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => bin2hex($value),
                            ];
                            break;

                        case ColumnType::MYSQL_TYPE_SHORT:
                            $value = $reader->readUint16();
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => $value,
                            ];
                            break;

                        case ColumnType::MYSQL_TYPE_LONG:
                            $value = $reader->readUint32();
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => $value,
                            ];
                            break;

                        case ColumnType::MYSQL_TYPE_VARCHAR:
                            $len = $reader->readUint8();
                            $data = $reader->readData($len);
                            $body['column'][] = [
                                'idx' => $idx,
                                'type' => $columnTypeName,
                                'value' => bin2hex($data),
                            ];
                            break;

                        default:
                            throw new \Exception("unhandled column type {$columnTypeName}");
                    }
                }
                break;

        }
        if (!isset($body))
        {
            throw new \Exception("body missing: $eventTypeName");
        }

        return [
            'header' => $eventHeader,
            'eventTypeName' => $eventTypeName,
            'body' => $body,
//            'headerData' => bin2hex($eventHeaderData),
//            'bodyData' => bin2hex($eventBodyData),
        ];
    }
}
