<?php

namespace mitch\schemacompare\providers;

use mitch\schemacompare\Object;
use mitch\schemacompare\Schema;

abstract class SchemaProvider extends Object
{
    public $typeConfig = '';
//    /**
//     * Must translate db type to local yml type such as int => integer
//     * @param $type
//     * @return mixed
//     */
//    public function dbTypeToLocal($type){
//        $config = require __DIR__ . '/../config/type-mappings.php';
//        $config = $config[$this->typeConfig];
//        return $config[$type];
//    }

    /**
     * Parse string. For example: article(id) return ['article', 'id']
     * @param $type
     * @return array
     */
    public function parseFuncString($type)
    {
        preg_match("/([A-z_0-9,]*?)\\(([A-z_0-9,]*?)\\)/", $type, $matches);
        if (count($matches) === 3) {
            return [$matches[1], $matches[2]];
        }
        return null;
    }

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