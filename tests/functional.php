<?php
require '../vendor/autoload.php';

use PHPUnit\Framework\TestCase;


class functionalTests extends TestCase
{

    function testImports()
    {
        $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_PERSISTENT => true]);

        //unlink('mysqlitedb.db');
        //$db = new SQLite3('mysqlitedb.db');
        $db->exec('CREATE TABLE roles (id INTEGER, name STRING)');


        $csvPath = __DIR__ . "/csv/";
        $importer = new \csvImporter\csvImporter($db, $csvPath);

        $configs = [

            [
                'table'  => 'roles',
                'fields' => [
                    'RoleId'   => 'id',
                    'RoleName' => 'name',
                ],
                'file'   => 'Roles.csv',
            ],

        ];


        $importer->run($configs);
    }
}
