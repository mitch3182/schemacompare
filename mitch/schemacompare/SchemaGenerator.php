<?php

namespace mitch\schemacompare;

abstract class SchemaGenerator extends Object
{
    abstract public function DropColumn(Column $one);
    abstract public function DropFk(ForeignKey $one);
    abstract public function DropTable(Table $one);
    abstract public function AlterColumn(Column $one);
    abstract public function AddColumn(Column $one);
    abstract public function CreateTable(Table $one);
    abstract public function CreateFk(ForeignKey $one);

    public function CreateFkName(ForeignKey $fk)
    {
        return 'fk_' . $fk->table . '_' . $fk->column;
    }

    abstract public function migrate($execute = false);
}