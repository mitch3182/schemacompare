<?php

require 'vendor/autoload.php';

use mitch\schemacompare\MysqlSchemaGenerator;
use mitch\schemacompare\Database;
use mitch\schemacompare\DbConnectionParams;
use mitch\schemacompare\MysqlSchemaProvider;
use mitch\schemacompare\YamlSchemaProvider;
use mitch\schemacompare\SchemaDump;
use mitch\schemacompare\Yii1SchemaGenerator;
use mitch\schemacompare\SchemaCompare;




use Symfony\Component\Yaml\Yaml;

spl_autoload_register(function ($name) {
    $filename = str_replace('\\', '/', $name) . '.php';
    if(file_exists($filename)){
        require $filename;
    }
});

//$schemaProvider = new MysqlSchemaProvider(['database' => 'portal', 'password' => '1234']);
//$schema = $schemaProvider->getSchema();
//YamlSchemaProvider::DumpSchema($schema, 'schema1.yml');
//$yamlSchemaProvider = new YamlSchemaProvider(['path' => 'schema1.yml']);
//$schema = $yamlSchemaProvider->getSchema();
//YamlSchemaProvider::DumpSchema($schema, 'schema2.yml');

$database = new Database([
    'user' => 'root',
    'password' => '',
    'database' => 'portal',
]);

$mysqlSchemaProvider = new MysqlSchemaProvider(['database' => $database]);
$mysqlschema = $mysqlSchemaProvider->getSchema();

//SchemaDump::Dump($mysqlschema, 'portal.yml');
$yamlSchemaProvider = new YamlSchemaProvider(['path' => 'portal.yml']);
$newSchema = $yamlSchemaProvider->getSchema();

SchemaDump::Dump($newSchema, 'portalc.yml');

$generator = new Yii1SchemaGenerator(['database' => $database]);

$compare = new SchemaCompare([
    'schema1' => $mysqlschema,
    'schema2' => $newSchema,
    'database' => $database,
    'generator' => $generator,
    'database' => $database,
]);

$compare->compare();

//print_r($generator->queries);


