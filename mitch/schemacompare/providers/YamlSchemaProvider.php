<?php

namespace mitch\schemacompare\providers;

use mitch\schemacompare\Table;
use mitch\schemacompare\Column;
use mitch\schemacompare\ForeignKey;

class YamlSchemaProvider extends SchemaProvider
{
    public $path = 'schema.yml';

//    public function dbTypeToLocal($type)
//    {
//        return $type;
//    }

    public function prepareSchema()
    {
        $data = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->path));

        foreach ($data as $tableName => $tableInfo) {

            $tableModel = new Table();
            $tableModel->name = $tableName;
            $tableModel->pk = isset($tableInfo['pk']) ? $tableInfo['pk'] : 'id';
            $columnsInfo = $tableInfo['columns'];

            foreach ($columnsInfo as $columnName => $columnInfo) {

                $columnModel = new Column;
                $columnModel->table = $tableModel;

                $columnModel->name = $columnName;
                $columnModel->dbType = isset($columnInfo['type']) ? $columnInfo['type'] : 'unknown_type';
                $columnModel->notNull = isset($columnInfo['notNull']) ? (bool) $columnInfo['notNull'] : false;
                $columnModel->default = isset($columnInfo['default']) ? $columnInfo['default'] : null;

                if(!empty($columnInfo['length'])){
                    $columnModel->length = $columnInfo['length'];
                }

                $columnModel->extra = isset($columnInfo['extra']) ? $columnInfo['extra'] : null;

                $columnModel->unsigned = isset($columnInfo['unsigned']) ? $columnInfo['unsigned'] : null;

                /**
                 * Check for timestamp column. It can not be timestamp while notNull and not have default value.
                 * There are some auto generate extra: on update CURRENT_TIMESTAMP and it can give you bad behavior
                 * in second start of comparator
                 **/
                if($columnModel->dbType == 'timestamp' and $columnModel->notNull and empty($columnModel->default)){
                    throw new \Exception("You must specify default value for notNull timestamp");
                }



                $tableModel->addColumn($columnModel);
            }


            if (isset($tableInfo['fks'])) {

                foreach ($tableInfo['fks'] as $sourceColname => $keyInfo) {

                    list($keyInfo, $onDelete, $onUpdate) = explode(':', $keyInfo);
                    list($m1, $m2) = $this->parseFuncString($keyInfo);

                    if (isset($m1) && isset($m2)) {

                        $col = $tableModel->getColumn($sourceColname);

                        if ($col == null) {
                            throw new \Exception("FK ERROR: Такой колонки нет {$tableName}:{$sourceColname}");
                        }

                        $fk = new ForeignKey([
                            'table' => $tableName,
                            'column' => $sourceColname,
                            'refTable' => $m1,
                            'refColumn' => $m2,
                            'onDelete' => $onDelete,
                            'onUpdate' => $onUpdate,
                        ]);

                        $col->dependencies[] = $fk;
                        $tableModel->fks[] = $fk;
                    } else {

                    }
                }
            }

            $this->schema->addTable($tableModel);
        }
    }
}