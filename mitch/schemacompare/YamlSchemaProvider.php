<?php

namespace mitch\schemacompare;

class YamlSchemaProvider extends SchemaProvider
{
    public $path = 'schema.yml';

    public function prepareSchema()
    {
        $data = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($this->path));

        foreach ($data as $tableName => $tableInfo) {

            $tableModel = new Table();
            $tableModel->name = $tableName;
            $tableModel->pk = isset($tableInfo['pk']) ? $tableInfo['pk'] : null;
            $columnsInfo = $tableInfo['columns'];

            foreach ($columnsInfo as $columnName => $columnInfo) {

                $columnModel = new Column;
                $columnModel->table = $tableModel;

                $columnModel->name = $columnName;
                $columnModel->dbType = isset($columnInfo['type']) ? $columnInfo['type'] : 'unknown_type';
                $columnModel->notNull = isset($columnInfo['notNull']) ? (bool) $columnInfo['notNull'] : false;
                $columnModel->default = isset($columnInfo['default']) ? $columnInfo['default'] : null;

                $columnModel->extra = isset($columnInfo['extra']) ? $columnInfo['extra'] : null;

                $tableModel->addColumn($columnModel);
            }


            if(isset($tableInfo['fks'])){

                foreach ($tableInfo['fks'] as $sourceColname => $keyInfo) {

                    preg_match("/([A-z_]*?)\\(([A-z_]*?)\\)/", $keyInfo, $matches);

                    if(isset($matches[1]) && isset($matches[2])){

                        $col = $tableModel->getColumn($sourceColname);

                        if($col == null){
                            throw new Exception("FK ERROR: Такой колонки нет {$tableName}:{$sourceColname}");
                        }

                        $fk = new ForeignKey([
                            'table' => $tableName,
                            'column' => $sourceColname,
                            'refTable' => $matches[1],
                            'refColumn' => $matches[2],
                        ]);

                        $col->dependencies[] = $fk;
                        $tableModel->fks[] = $fk;
                    }else{
                        
                    }
                }
            }

            $this->schema->tables[] = $tableModel;
        }
    }
}