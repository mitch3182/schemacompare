<?php

namespace mitch\schemacompare;

class Schema
{
    /** @var Table[] */
    public $tables = [];

    /**
     * Найти таблицу
     * @param $name
     * @return null|Table
     */
    public function getTable($name)
    {
        if (isset($this->tables[$name])) {
            return $this->tables[$name];
        }

        return null;
    }

    /**
     * Добавить таблицу в схему
     * @param Table $table
     * @throws \Exception
     */
    public function addTable(Table $table)
    {
        if (empty($table->name)) {
            throw new \Exception('Table name can not be empty');
        }

        $this->tables[$table->name] = $table;
    }

    /**
     * Получить информацию о зависимости колонки
     * @param Column $column
     * @return ForeignKey[]
     */
    public function getColumnDependency(Column $column)
    {

        $output = [];

        foreach ($this->tables as $table) {
            foreach ($table->fks as $fk) {
                if ($fk->refTable == $column->table->name && $fk->refColumn == $column->name) {
                    $output[] = $fk;
                }
            }
        }

        return $output;
    }
}