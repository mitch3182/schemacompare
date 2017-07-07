<?php

require __DIR__ . '/../autoload.php';

use mitch\schemacompare\MysqlSchemaGenerator;
use mitch\schemacompare\providers\MysqlSchemaProvider;
use mitch\schemacompare\providers\YamlSchemaProvider;
use mitch\schemacompare\SchemaCompare;
use mitch\schemacompare\Database;

class Tester{

    public $datatabase;
    public $data_dir = __DIR__ . '/data';

    public function run(){
        $this->InitDb();
        $this->CreateBaseSchema();
        $this->DeleteTableRightOrder();
    }

    public function InitDb(){
        echo "init db\n";
        exec('mysql -uroot -e "drop database shemacompare_test"');
        exec('mysql -uroot -e "create database shemacompare_test charset utf8"');

        $this->datatabase = new Database(['user' => 'root', 'database' => 'shemacompare_test']);
    }

    public function getDbSchema(){
        $provider = new MysqlSchemaProvider(['database' => $this->datatabase]);
        return $provider->getSchema();
    }

    public function getYmlSchema($file){
        $provider = new YamlSchemaProvider(['path' => $this->data_dir . '/' . $file]);
        return $provider->getSchema();
    }

    public function applySchema($schema_file){

        // first
        $comparator = new SchemaCompare([
            'schema1' => $this->getDbSchema(),
            'schema2' => $this->getYmlSchema($schema_file),
            'database' => $this->datatabase,
            'generator' => new MysqlSchemaGenerator(),
        ]);

        $generator = $comparator->compare();
        $generator->database = $this->datatabase;
        $generator->migrate(true);

        // second
        $comparator = new SchemaCompare([
            'schema1' => $this->getDbSchema(),
            'schema2' => $this->getYmlSchema($schema_file),
            'database' => $this->datatabase,
            'generator' => new MysqlSchemaGenerator(),
        ]);

        /** @var MysqlSchemaGenerator $generator */
        $generator = $comparator->compare();
        $generator->database = $this->datatabase;
        $generator->migrate();
        if(count($generator->queries) > 0){
            throw new Exception('something wrong with comparator');
        }
    }

    public function CreateBaseSchema(){
        $this->applySchema('schema.yml');
    }

    /**
     * Удаление таблиц в правильном порядке
     * зависимая таблица ниже основной и наоборот
     */
    public function DeleteTableRightOrder(){

        // restore
        echo "==================================\n";
        $this->applySchema('schema.yml');

        // delete fk and delete table
        echo "==================================\n";
        $this->applySchema('schema-delete-tables.yml');

//        // restore
//        echo "==================================\n";
//        $this->applySchema('schema.yml');

//        // delete fk, column and table
//        echo "==================================\n";
//        $this->applySchema('schema-delete-tables-with-related-column.yml');
//
//        // restore
//        echo "==================================\n";
//        $this->applySchema('schema.yml');
//
//        // delete all
//        echo "==================================\n";
//        $this->applySchema('schema-empty.yml');
    }

    /**
     * Создание таблиц - проставление порядка
     * тестируется 2 случая - таблицы в файле в правильном порядке и таблицы в неправильном порядке
     * (зависимая таблица выше основной)
     */
    public function CreateTableRightOrder(){

    }

    public function DeleteTableAndRelatedColumn(){

    }
}

$tester = new Tester();
$tester->run();