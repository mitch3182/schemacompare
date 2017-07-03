<?php

/**
 * Возвращает именованные аргументы командной строки
 * @param $argv
 * @return array
 */
function getNamedArgs($argv){
    $output = [];

    foreach($argv as $arg){

        if(strpos($arg, '--') === 0){
            $arg = substr($arg, 2);
            list($k, $v) = explode('=', $arg);
            $output[$k] = $v;
        }
    }

    return $output;
}

function showusage(){
    echo "using: command [params]\n";
    echo "inspectdb path_to_save_schema_yml --database [--host] [--user] [--password] [--port] \n";
    echo "sync path_to_schema_yml --database [--host] [--user] [--password] [--port] \n";
}

require 'vendor/autoload.php';

spl_autoload_register(function ($name) {
    $filename = str_replace('\\', '/', $name) . '.php';
    if(file_exists($filename)){
        require $filename;
    }
});

echo "Welcome to migration script\n";

if(count($argv) < 2){
    showusage();
    die();
}

$command = $argv[1];
$namedArgs = getNamedArgs($argv);

$db = new \mitch\schemacompare\Database([
    'database' => isset($namedArgs['database']) ? $namedArgs['database'] : null,
    'user' => isset($namedArgs['user']) ? $namedArgs['user'] : null,
    'passowrd' => isset($namedArgs['password']) ? $namedArgs['password'] : null,
    'port' => isset($namedArgs['port']) ? $namedArgs['port'] : null,
    'host' => isset($namedArgs['host']) ? $namedArgs['host'] : null,
]);

if($command == 'inspectdb'){

    if(empty($argv[2])){
        die("You must specify path\n");
    }

    $path = $argv[2];

    $provider = new \mitch\schemacompare\providers\MysqlSchemaProvider(['database' => $db]);
    $schema = $provider->getSchema();
    \mitch\schemacompare\SchemaDump::Dump($schema, $path);
}

if($command == 'sync'){

    if(empty($argv[2])){
        die("You must specify path\n");
    }

    $path = $argv[2];

    $provider = new \mitch\schemacompare\providers\MysqlSchemaProvider(['database' => $db]);
    $schema1 = $provider->getSchema();

    $schema2 = new \mitch\schemacompare\providers\YamlSchemaProvider(['path' => $path]);
    $schema2 = $schema2->getSchema();

    if (!isset($namedArgs['generator'])){
        $namedArgs['generator'] = 'raw';
    }

    if($namedArgs['generator'] == 'raw'){
        $generator = new \mitch\schemacompare\MysqlSchemaGenerator(['database' => $db]);
    }
    if($namedArgs['generator'] == 'yii1'){
        if(!isset($namedArgs['path'])){
            die("--path= required\n");
        }
        $generator = new \mitch\schemacompare\Yii1SchemaGenerator(['database' => $db, 'path' => $namedArgs['path']]);
    }

    if($namedArgs['generator'] == 'yii2'){
        if(!isset($namedArgs['path'])){
            die("--path= required\n");
        }
        $generator = new \mitch\schemacompare\Yii2SchemaGenerator(['database' => $db, 'path' => $namedArgs['path']]);
    }

    $compare = new \mitch\schemacompare\SchemaCompare([
        'schema1' => $schema1,
        'schema2' => $schema2,
        'generator' => $generator,
        'database' => $db,
    ]);

    $generator = $compare->compare();
    $generator->migrate();
}