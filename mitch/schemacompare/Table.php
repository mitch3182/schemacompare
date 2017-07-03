<?php

namespace mitch\schemacompare;

class Table
{
    /** @var Column[] */
    public $columns = [];
    public $name;
    public $indexes;

    public $delete = false;

    /** @var ForeignKey[] */
    public $fks = [];

    /** @var Table[] */
    public $dependencies = [];
    public $pk = null;

    public function addColumn(Column $column)
    {
        $this->columns[] = $column;
    }

    /**
     * Возвращает модель колонки
     * @param $name
     * @return Column|null
     */
    public function getColumn($name)
    {

        foreach ($this->columns as &$col) {
            if ($col->name == $name) {
                return $col;
            }
        }

        return null;
    }

    public function hasFk(ForeignKey $fk){
        foreach($this->fks as $fk1){
            if(
                $fk1->table == $fk->table
                && $fk1->column == $fk->column
                && $fk1->refTable == $fk->refTable
                && $fk1->refColumn == $fk->refColumn
            ){
                return true;
            }
        }
        return false;
    }
}