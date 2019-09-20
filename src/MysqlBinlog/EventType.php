<?php


namespace MysqlBinlog;


class EventType
{
    public const UNKNOWN_EVENT = 0x00;
    public const START_EVENT_V3 = 0x01;
    public const QUERY_EVENT = 0x02;
    public const STOP_EVENT = 0x03;
    public const ROTATE_EVENT = 0x04;
    public const INTVAR_EVENT = 0x05;
    public const LOAD_EVENT = 0x06;
    public const SLAVE_EVENT = 0x07;
    public const CREATE_FILE_EVENT = 0x08;
    public const APPEND_BLOCK_EVENT = 0x09;
    public const EXEC_LOAD_EVENT = 0x0a;
    public const DELETE_FILE_EVENT = 0x0b;
    public const NEW_LOAD_EVENT = 0x0c;
    public const RAND_EVENT = 0x0d;
    public const USER_VAR_EVENT = 0x0e;
    public const FORMAT_DESCRIPTION_EVENT = 0x0f;
    public const XID_EVENT = 0x10;
    public const BEGIN_LOAD_QUERY_EVENT = 0x11;
    public const EXECUTE_LOAD_QUERY_EVENT = 0x12;
    public const TABLE_MAP_EVENT = 0x13;
    public const WRITE_ROWS_EVENTv0 = 0x14;
    public const UPDATE_ROWS_EVENTv0 = 0x15;
    public const DELETE_ROWS_EVENTv0 = 0x16;
    public const WRITE_ROWS_EVENTv1 = 0x17;
    public const UPDATE_ROWS_EVENTv1 = 0x18;
    public const DELETE_ROWS_EVENTv1 = 0x19;
    public const INCIDENT_EVENT = 0x1a;
    public const HEARTBEAT_EVENT = 0x1b;
    public const IGNORABLE_EVENT = 0x1c;
    public const ROWS_QUERY_EVENT = 0x1d;
    public const WRITE_ROWS_EVENTv2 = 0x1e;
    public const UPDATE_ROWS_EVENTv2 = 0x1f;
    public const DELETE_ROWS_EVENTv2 = 0x20;
    public const GTID_EVENT = 0x21;
    public const ANONYMOUS_GTID_EVENT = 0x22;
    public const PREVIOUS_GTIDS_EVENT = 0x23;

    private static $eventTypeNames = [
        0x00 => 'UNKNOWN_EVENT',
        0x01 => 'START_EVENT_V3',
        0x02 => 'QUERY_EVENT',
        0x03 => 'STOP_EVENT',
        0x04 => 'ROTATE_EVENT',
        0x05 => 'INTVAR_EVENT',
        0x06 => 'LOAD_EVENT',
        0x07 => 'SLAVE_EVENT',
        0x08 => 'CREATE_FILE_EVENT',
        0x09 => 'APPEND_BLOCK_EVENT',
        0x0a => 'EXEC_LOAD_EVENT',
        0x0b => 'DELETE_FILE_EVENT',
        0x0c => 'NEW_LOAD_EVENT',
        0x0d => 'RAND_EVENT',
        0x0e => 'USER_VAR_EVENT',
        0x0f => 'FORMAT_DESCRIPTION_EVENT',
        0x10 => 'XID_EVENT',
        0x11 => 'BEGIN_LOAD_QUERY_EVENT',
        0x12 => 'EXECUTE_LOAD_QUERY_EVENT',
        0x13 => 'TABLE_MAP_EVENT',
        0x14 => 'WRITE_ROWS_EVENTv0',
        0x15 => 'UPDATE_ROWS_EVENTv0',
        0x16 => 'DELETE_ROWS_EVENTv0',
        0x17 => 'WRITE_ROWS_EVENTv1',
        0x18 => 'UPDATE_ROWS_EVENTv1',
        0x19 => 'DELETE_ROWS_EVENTv1',
        0x1a => 'INCIDENT_EVENT',
        0x1b => 'HEARTBEAT_EVENT',
        0x1c => 'IGNORABLE_EVENT',
        0x1d => 'ROWS_QUERY_EVENT',
        0x1e => 'WRITE_ROWS_EVENTv2',
        0x1f => 'UPDATE_ROWS_EVENTv2',
        0x20 => 'DELETE_ROWS_EVENTv2',
        0x21 => 'GTID_EVENT',
        0x22 => 'ANONYMOUS_GTID_EVENT',
        0x23 => 'PREVIOUS_GTIDS_EVENT',
    ];

    public static function resolveEventType($eventType)
    {
        return self::$eventTypeNames[$eventType];
    }
}
