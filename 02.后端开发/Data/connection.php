<?php

require_once BASE_PATH . '/Extension/Medoo.php';
require_once BASE_PATH . '/Data/config.php';

use Medoo\Medoo;  //namespace

// 初始化配置
$database_medoo = new Medoo([
    // required
    'database_type' => DB_TYPE,
    'database_name' => DB_NAME,
    'server' => DB_HOST,
    'username' => DB_USER,
    'password' => DB_PASSWORD,
    // [optional]
    'charset' => DB_CHARSET,
    'port' => DB_PORT,
        // [optional] Table prefix
//	'prefix' => 'PREFIX_', 
        // [optional] Enable logging (Logging is disabled by default for better performance)
//	'logging' => true,
        // [optional] MySQL socket (shouldn't be used with server and port)
//	'socket' => '/tmp/mysql.sock',
        // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
//	'option' => [
//		PDO::ATTR_CASE => PDO::CASE_NATURAL
//	],
        // [optional] Medoo will execute those commands after connected to the database for initialization
//	'command' => [
//		'SET SQL_MODE=ANSI_QUOTES'
//	]
        ]);
