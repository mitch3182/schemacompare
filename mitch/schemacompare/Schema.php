<?php

namespace mitch\schemacompare;

class Schema
{
    /** @var Table[] */
    public $tables = [];

    /**
     * @param $name
     * @return null|Table
     */
    public function getTable($name){
        foreach($this->tables as &$table){
            if($table->name == $name){
                return $table;
            }
        }

        return null;
    }

    /**
     * @param Column $column
     * @return ForeignKey[]
     */
    public function getColumnDependency(Column $column){

        $output = [];

        foreach($this->tables as $table){
            foreach($table->fks as $fk){
                if($fk->refTable == $column->table->name && $fk->refColumn == $column->name){
                    $output[] = $fk;
                }
            }
        }

        return $output;
    }
}