<?php
define("URL_BASE", "http://localhost");
define("URL_CSS", URL_BASE."/src/css");
define("URL_IMAGES", URL_BASE."/src/img");
define("NOME_EMPRESA", "NOVA");
define("VERSAO", "1.0.1");
define("COPYRIGHT", "2018 - 2020");


define('DATA_LAYER_CONFIG', [
    'radius' => [
        'driver' => 'mysql',
        'host' => '45.164.81.42',
        'port' => '3307',
        'dbname' => 'mkradius',
        'username' => 'sispro',
        'passwd' => 'sispro',
        'options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
        ],
    ],
    'interno' => [
        'driver' => 'mysql',
        'host' => '191.252.181.161',
        'port' => '3306',
        'dbname' => 'sispro',
        'username' => 'sispro',
        'passwd' => 'sispro',
        'options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
        ],
    ]
]);