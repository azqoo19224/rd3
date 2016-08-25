<?php
class DB
{
    public static $db;

    public static function pdoConnect()
    {
        $config['db']['dsn'] = 'mysql:host=localhost; dbname=Api; charset=utf8';
        $config['db']['user'] = 'root';
        $config['db']['password'] = '0000';

        $db = new PDO($config['db']['dsn'], $config['db']['user'], $config['db']['password']);

        DB::$db = $db;
    }
}
