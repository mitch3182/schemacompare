<?php

namespace mitch\schemacompare;


class MysqlSchemaProvider extends SchemaProvider
{

    /** @var Database */
    public $database;

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

                $columnModel = new Column;
                $columnModel->table = $tableModel;

                $columnModel->name = $columnInfo['Field'];
                $columnModel->dbType = $columnInfo['Type'];
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