<?php

require 'autoload.php';

$db = new \mitch\schemacompare\Database([
    'database' => 'portal',
    'password' => '1234',
]);

$provider = new \mitch\schemacompare\providers\MysqlSchemaProvider([
    'database' => $db,
]);

print '<pre>';
print_r($db->fetchIndexes());
exit();