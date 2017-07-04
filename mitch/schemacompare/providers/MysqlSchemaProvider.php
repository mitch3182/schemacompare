<?php

namespace mitch\schemacompare\providers;

use mitch\schemacompare\Database;
use mitch\schemacompare\Table;
use mitch\schemacompare\Column;
use mitch\schemacompare\ForeignKey;

class MysqlSchemaProvider extends SchemaProvider
{

    /** @var Database */
    public $database;

    public $typeConfig = 'mysql';

    /**
     * @param $columnInfo
     * @return Column
     */
    public function prepareColumn($columnInfo){
        $length = null;
        $columnModel = new Column;

        $columnModel->name = $columnInfo['Field'];

        $dbtype = $this->parseFuncString($columnInfo['Type']);

        if($dbtype != null){
            list($dbtype, $length) = $dbtype;
        }else{
            $dbtype = $columnInfo['Type'];
        }
        if(strpos($dbtype, 'unsigned') !== false){
            $dbtype = trim(str_replace('unsigned', '', $dbtype));
            $columnModel->unsigned = true;
        }

        $columnModel->dbType = $dbtype;
        $columnModel->length = $length != null ? $length : null;
        $columnModel->notNull = $columnInfo['Null'] == 'NO';
        $columnModel->default = $columnInfo['Default'];
        $columnModel->extra = $columnInfo['Extra'];

        return $columnModel;
    }

    /**
     * @param $tableName
     * @return Table|bool
     */
    public function prepareTable($tableName){
        $tableModel = new Table();
        $tableModel->name = $tableName;
        $columnsInfo = $this->database->getTableInfo($tableName);

        if ($columnsInfo === false) {
            return false;
        }

        foreach ($columnsInfo as $columnInfo) {
            $columnModel = $this->prepareColumn($columnInfo);
            $columnModel->table = $tableModel;
            $tableModel->addColumn($columnModel);
        }

        $keysInfo = $this->database->getForeignKeys($tableName);

        foreach ($keysInfo as $keyInfo) {
            if ($keyInfo['CONSTRAINT_NAME'] == 'PRIMARY') {
                $tableModel->pk = $keyInfo['COLUMN_NAME'];
            } else {
                if (isset($keyInfo['TABLE_NAME'])
                    && isset($keyInfo['COLUMN_NAME'])
                    && isset($keyInfo['REFERENCED_TABLE_NAME'])
                    && isset($keyInfo['REFERENCED_COLUMN_NAME'])
                ) {

                    $col = $tableModel->getColumn($keyInfo['COLUMN_NAME']);

                    $fk = new ForeignKey([
                        'table' => $keyInfo['TABLE_NAME'],
                        'column' => $keyInfo['COLUMN_NAME'],
                        'refTable' => $keyInfo['REFERENCED_TABLE_NAME'],
                        'refColumn' => $keyInfo['REFERENCED_COLUMN_NAME'],
                        'onDelete' => strtolower($keyInfo['DELETE_RULE']),
                        'onUpdate' => strtolower($keyInfo['UPDATE_RULE']),
                    ]);

                    $col->fks[] = $fk;
                    $tableModel->fks[] = $fk;
                }

            }
        }
        return $tableModel;
    }

    public function prepareSchema()
    {
        foreach ($this->database->getTableNames() as $tableName) {
            $tableModel = $this->prepareTable($tableName);
            if($tableModel !== false){
                $this->schema->tables[] = $tableModel;
            }
        }
    }

}