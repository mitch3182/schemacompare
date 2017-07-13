<?php

namespace mitch\schemacompare\providers;

use mitch\schemacompare\Table;
use mitch\schemacompare\Column;
use mitch\schemacompare\ForeignKey;
use Symfony\Component\Yaml\Yaml;

class YamlSchemaProvider extends SchemaProvider
{
    public $path = 'schema.yml';

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function prepareSchema()
    {
        $data = Yaml::parse(file_get_contents($this->path));
        if(!is_array($data)){
            $data = [];
        }

        foreach ($data as $tableName => $tableInfo) {

            $tableModel = new Table();
            $tableModel->name = $tableName;
            $tableModel->pk = isset($tableInfo['pk']) ? $tableInfo['pk'] : 'id';
            $columnsInfo = $tableInfo['columns'];

            foreach ($columnsInfo as $columnName => $columnInfo) {

                // short syntax
                if(is_string($columnInfo)){
                    if($columnInfo === 'pk'){
                        $columnInfo = [
                            'length' => '11',
                            'type' => 'int',
                            'notNull' => true,
                            'extra' => 'auto_increment',
                        ];
                        $tableModel->pk = 'id';
                    }else{
                        $parts = explode(':', $columnInfo);
                        $columnInfo = [];
                        foreach($parts as $part){
//                            echo $part . "\n";
                            if(strpos($part, '(') !== false){
                                list($t, $l) = $this->parseFuncString($part);

                                if($t === 'string'){
                                    $columnInfo['type'] = 'varchar';
                                    $columnInfo['length'] = $l;
                                }
                                if($t === 'int'){
                                    $columnInfo['type'] = 'int';
                                    $columnInfo['length'] = $l;
                                }
                                if($t === 'd'){
                                    $columnInfo['default'] = $l;
                                }
                            }

                            if($part === 'notNull'){
                                $columnInfo['notNull'] = true;
                            }

                            if($part === 'int'){
                                $columnInfo['type'] = 'int';
                                $columnInfo['length'] = 11;
                            }

                            // Проставление типов без length
                            if(in_array($part, ['datetime', 'date', 'timestamp', 'text', 'float', 'double'])){
                                $columnInfo['type'] = $part;
                            }
                        }
                    }
                }

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
                if($columnModel->dbType == 'timestamp' && $columnModel->notNull && empty($columnModel->default)){
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