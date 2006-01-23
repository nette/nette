<?php

/**
 * This file is part of the Nette Framework (http://nette.texy.info/)
 *
 * Copyright (c) 2005-2007 David Grudl aka -dgx- <dave@dgx.cz>
 *
 * @version  $Revision: 51 $ $Date: 2007-04-11 20:34:53 +0200 (st, 11 IV 2007) $
 * @package  Nette
 */



class NSQLiteStorage implements ILockingStorage
{
    /** @var resource[] */
    protected static $db;

    /** @var resource */
    protected $conn;

    /** @var string */
    protected $table;


    /**
     * Inicializes new SQlite-based storage engine
     * @param string  path to database
     */
    public function __construct($database, $table)
    {
        if (!isset(self::$db[$database]))
            self::$db[$database] = sqlite_open($database);

        $this->conn = self::$db[$database];
        $this->table = $table;

        if (!sqlite_num_rows(sqlite_query($this->conn, "PRAGMA table_info([$table])"))) {
            sqlite_query(
                $this->conn,
                "CREATE TABLE [$table] (
                [key] VARCHAR(255) NOT NULL PRIMARY KEY,
                [value] TEXT NULL,
                [accessed] TIMESTAMP NULL,
                [locked] TIMESTAMP NULL
                );"
            );
        }
    }


    public function read($id)
    {
        $s = sqlite_single_query(
            $conn,
            "SELECT [value] FROM [" . $this->table . "] WHERE [key]='" . sqlite_escape_string($id) . "'",
            TRUE
        );
        if (!is_string($s) || $s === '') return NULL;
        $value = unserialize($s);
        if ($value === FALSE && $s !== 'b:0;') return NULL;
        return $value;
    }


    public function write($id, $value, $meta=NULL)
    {
        $id = sqlite_escape_string($id);
        if ($value === NULL) {
            sqlite_query($this->conn, "DELETE FROM [" . $this->table . "] WHERE [key]='" . $id . "'");
        } else {
            $value = sqlite_escape_string(serialize($value));
            sqlite_query($this->conn, "REPLACE INTO [" . $this->table . "] ([key], [value]) VALUES ('" . $id ."', '" . $value. "')");
        }
        return sqlite_last_error($this->conn) == 0;
    }


    public function lock($id, $forWrite)
    {
        trigger_error('Not implemented yet.', E_USER_WARNING);
    }


    public function unlock($id)
    {
        trigger_error('Not implemented yet.', E_USER_WARNING);
    }

}
