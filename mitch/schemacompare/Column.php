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
    public $length;
    public $unsigned;

    public $delete = false;

    public $fks = [];
    /** @var Column[] */
    public $dependencies = [];

//    public $mappingDbTypes = [
//        'integer' => 'int(11)';
//    ];

    public function compare($otherColumn){
        $fields = ['name', 'dbType', 'comment', 'notNull', 'extra', 'default', 'length', 'unsigned'];
        foreach($fields as $field){
            $eq = $this->$field == $otherColumn->$field;
            if (!$eq){
                echo "compare field {$otherColumn->name} mismatch $field\n";
                return false;
            }
        }
        return true;
    }
}