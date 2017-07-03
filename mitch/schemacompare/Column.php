<?php

namespace mitch\schemacompare;

class Column
{
    /** @var Table */
    public $table;
    public $name;
    public $dbType;
    public $comment;
    public $notNull;
    public $extra;
    public $default;

    public $delete = false;

    public $fks = [];
    /** @var Column[] */
    public $dependencies = [];

//    public $mappingDbTypes = [
//        'integer' => 'int(11)';
//    ];

    public function compare($otherColumn){
        $fields = ['name', 'dbType', 'comment', 'notNull', 'extra', 'default'];
        $equal = true;
        foreach($fields as $field){
            $equal &= $this->$field == $otherColumn->$field;
        }
        return $equal;
    }
}