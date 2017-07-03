<?php

namespace mitch\schemacompare;

abstract class SchemaProvider extends Object
{
    /** @return Schema */
    public function getSchema()
    {
        $this->schema = new Schema();
        $this->prepareSchema();
        $this->checkSchema();
        return $this->schema;
    }

    /** @var Schema */
    public $schema;

    abstract public function prepareSchema();

    public function checkSchema()
    {
        $schema = $this->schema;

        foreach ($schema->tables as &$table) {
            foreach ($table->fks as $fk) {

                $refTable = $schema->getTable($fk->refTable);

                if ($refTable == null) {
                    echo "Нарушены условия FK - не найдена искомая таблица $fk->refTable у зависимости {$fk->table}($fk->column)\n";
                    continue;
                }

                $refColumn = $refTable->getColumn($fk->refColumn);

                if ($refColumn == null) {
                    echo "Нарушены условия FK - не найдена искомая таблица $fk->refTable у зависимости {$fk->table}($fk->column)\n";
                    continue;
                }
            }
        }
    }
}