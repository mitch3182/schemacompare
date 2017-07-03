<?php

namespace mitch\schemacompare;


class ForeignKey extends Object
{
    public $table;
    public $column;
    public $refTable;
    public $refColumn;

    public $name;
}