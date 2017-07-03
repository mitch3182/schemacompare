<?php

namespace mitch\schemacompare;

class SchemaDump
{
    public static function Dump(Schema $schema, $path){
        $output = [];

        foreach ($schema->tables as $table) {

            $output[$table->name] = [
                'columns' => []
            ];

            $output[$table->name]['pk'] = $table->pk;

            foreach ($table->columns as $columName => $columnInfo) {
                $output[$table->name]['columns'][$columnInfo->name] = [
                    'type' => $columnInfo->dbType,
                    'notNull' => $columnInfo->notNull,
                ];

                if ( ! is_null($columnInfo->default)) {
                    $output[$table->name]['columns'][$columnInfo->name]['default'] = $columnInfo->default;
                }

                if ( ! is_null($columnInfo->extra) && $columnInfo->extra != null) {
                    $output[$table->name]['columns'][$columnInfo->name]['extra'] = $columnInfo->extra;
                }
            }


            foreach ($table->fks as $fk) {
                if ( ! isset($output[$table->name]['fks'])) {
                    $output[$table->name]['fks'] = [];
                }
                $output[$table->name]['fks'][$fk->column] = $fk->refTable . '(' . $fk->refColumn . ')';
            }
        }

        file_put_contents($path, \Symfony\Component\Yaml\Yaml::dump($output, 5));
    }
}