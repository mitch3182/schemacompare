<?php

namespace mitch\schemacompare\providers;

use mitch\schemacompare\Table;
use mitch\schemacompare\Column;
use mitch\schemacompare\ForeignKey;

class MysqlSchemaProvider extends SchemaProvider
{

    /** @var Database */
    public $database;

    public $typeConfig = 'mysql';

    public function prepareSchema()
    {
        foreach ($this->database->getTableNames() as $tableName) {

            $tableModel = new Table();
            $tableModel->name = $tableName;
            $columnsInfo = $this->database->getTableInfo($tableName);

            if ($columnsInfo === false) {
                continue; //  for views
            }

            foreach ($columnsInfo as $columnInfo) {

                $length = null;
                $columnModel = new Column;
                $columnModel->table = $tableModel;

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
                        ]);

                        $col->fks[] = $fk;
                        $tableModel->fks[] = $fk;
                    }

                }
            }
            $this->schema->tables[] = $tableModel;
        }
    }

}