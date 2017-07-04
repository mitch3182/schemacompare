<?php

namespace mitch\schemacompare;


class ForeignKey extends Object
{
    public $table;
    public $column;
    public $refTable;
    public $refColumn;

    public $onDelete = null;
    public $onUpdate = null;

    public $name;

    public function compare(ForeignKey $otherFk){
        $fields = ['table', 'column', 'refTable', 'refColumn', 'onDelete', 'onUpdate'];
        foreach($fields as $field){
            $eq = $this->$field == $otherFk->$field;
            if (!$eq){
                return false;
            }
        }
        return true;
    }
}