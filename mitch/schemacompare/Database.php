<?php

namespace mitch\schemacompare;

class Database
{

    public $conn;

    /** @var DbConnectionParams */
    public $dbConnectionParams;

    public function __construct(array $options = [])
    {
        $this->dbConnectionParams = new DbConnectionParams($options);

        $this->conn = mysqli_connect(
            $this->dbConnectionParams->host,
            $this->dbConnectionParams->user,
            $this->dbConnectionParams->password,
            $this->dbConnectionParams->database,
            $this->dbConnectionParams->port
        );

        if($this->conn === false){
            throw new Exception('Ошибка соединения с базой данных');
        }
    }

    public function getTableNames()
    {
        $q = "show full tables where Table_Type = 'BASE TABLE'";
        $res = mysqli_query($this->conn, $q);
        return array_map(function ($item) {
            return $item[0];
        }, mysqli_fetch_all($res));
    }

    protected function fetchAllAssoc($res)
    {
        $output = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $output[] = $row;
        }
        return $output;
    }

    public function getTableInfo($table)
    {
        $res = mysqli_query($this->conn, 'describe ' . $table);

        if ($res === false) {
            return false;
        }

        return $this->fetchAllAssoc($res);
    }

    public function getFkNameFromDb(ForeignKey $fk)
    {
        $q = "
         SELECT CONSTRAINT_NAME
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  TABLE_SCHEMA = '{$this->dbConnectionParams->database}' AND
  TABLE_NAME = '{$fk->table}' 
  and TABLE_NAME = '{$fk->table}' 
  and COLUMN_NAME = '{$fk->column}' 
  and REFERENCED_TABLE_NAME='{$fk->refTable}' 
  and REFERENCED_COLUMN_NAME='{$fk->refColumn}';";

        $res = mysqli_query($this->conn, $q);
        $field = mysqli_fetch_row($res);
        return $field[0];
    }

    public function fetchFkConstraints(){
        $q = "SELECT 
  kcu.TABLE_NAME,kcu.COLUMN_NAME,kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME,kcu.REFERENCED_COLUMN_NAME, rc.UPDATE_RULE, rc.DELETE_RULE
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
  left join information_schema.REFERENTIAL_CONSTRAINTS rc 
  on rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME and rc.CONSTRAINT_SCHEMA =  '{$this->dbConnectionParams->database}' 
  WHERE
  kcu.TABLE_SCHEMA = '{$this->dbConnectionParams->database}'";
        $res = mysqli_query($this->conn, $q);
        return $this->fetchAllAssoc($res);
    }

    public function getForeignKeys($table)
    {
        $q = "
         SELECT 
  kcu.TABLE_NAME,kcu.COLUMN_NAME,kcu.CONSTRAINT_NAME, kcu.REFERENCED_TABLE_NAME,kcu.REFERENCED_COLUMN_NAME, rc.UPDATE_RULE, rc.DELETE_RULE
FROM
  INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
  left join information_schema.REFERENTIAL_CONSTRAINTS rc on rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
WHERE
  kcu.TABLE_SCHEMA = '{$this->dbConnectionParams->database}' AND
  kcu.TABLE_NAME = '{$table}';
        ";

        $res = mysqli_query($this->conn, $q);
        return $this->fetchAllAssoc($res);
    }

    public function exec($command)
    {
        echo $command;
        $stm = mysqli_query($this->conn, $command);

        if ($stm === false) {
            print_r(mysqli_error($this->conn));
            die();
        }

        if ( ! is_array($stm)) {

        } else {
            return mysqli_fetch_row($stm);
        }
    }
}