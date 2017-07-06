<?php

namespace mitch\schemacompare;

class MysqlSchemaGenerator extends SchemaGenerator
{
    public $queries = [];
    /** @var Database */
    public $database;

    protected $_typeConfig = null;

    public function getType($type){

        if($this->_typeConfig == null){
            $typeConfig = require __DIR__ . '/config/type-mappings.php';
            $this->_typeConfig = array_flip(array_reverse($typeConfig['mysql']));
        }

        return $this->_typeConfig[$type];
    }

    public function ColumnDefinition(Column $column)
    {
        $notNull = $column->notNull ? 'not null' : 'null';
        $default = $column->default != null ? "default $column->default" : '';
        $extra = $column->extra;

        $dbType = $column->dbType;
//        $dbType = $this->getType($dbType);


        if($column->length){
            $dbType.= "({$column->length})";
        }

        if($column->unsigned){
            $dbType .= ' ' . 'unsigned';
        }

        return "{$column->name} {$dbType} {$notNull} {$default} {$extra}";
    }

    public function AlterColumn(Column $column)
    {
        $this->queries[] = "alter table {$column->table->name} modify COLUMN " . self::ColumnDefinition($column) . ";\n";
    }

    public function DropColumn(Column $column)
    {
        $this->queries[] = "alter table {$column->table->name} drop column {$column->name};\n";
    }

    public function AddColumn(Column $column)
    {
        $this->queries[] = "alter table {$column->table->name} add column " . self::ColumnDefinition($column) . ";\n";
    }

    public function DropTable(Table $table)
    {
        $this->queries[] = "drop table $table->name" . ";\n";
    }

    public function CreateTable(Table $table)
    {
        $columns = [];

        foreach ($table->columns as $one) {
            $columns[] = "\t" . self::ColumnDefinition($one);
        }

        $pk = $table->pk ? ", \nPRIMARY KEY($table->pk)\n" : '';

        $this->queries[] = "create table $table->name (" . "\n"
            . join(",\n", $columns)
            . $pk
            . "\n);\n";
    }

    public function CreateFk(ForeignKey $fk)
    {
        $fkName = self::CreateFkName($fk);
        $this->queries[] = "alter table $fk->table add constraint $fkName foreign key ($fk->column) references {$fk->refTable}({$fk->refColumn}) ON DELETE {$fk->onDelete} ON UPDATE {$fk->onUpdate};\n";
    }

    public function DropFk(ForeignKey $fk)
    {
        $this->queries[] = "alter table {$fk->table} DROP FOREIGN KEY {$fk->name};\n";
    }

    public function migrate($execute = false)
    {
        print_r($this->queries);

        if($execute){
            foreach($this->queries as $query){
                $this->database->exec($query);
            }
        }
    }
}