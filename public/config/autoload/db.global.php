<?php

$dbhost = 'localhost';
$dbname = 'gibson';
$dbport = '3306';
$username = $password = 'hacman';

if(isset($_SERVER['RDS_HOSTNAME'])) {

    $dbhost = $_SERVER['RDS_HOSTNAME'];
    $dbport = $_SERVER['RDS_PORT'];
    $dbname = $_SERVER['RDS_DB_NAME'];

    $username = $_SERVER['RDS_USERNAME'];
    $password = $_SERVER['RDS_PASSWORD'];
}

return [
    'db' => [
        'driver'         => 'Pdo',
        'dsn'            => $dsn = "mysql:host={$dbhost};port={$dbport};dbname={$dbname}",
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ],
        'username' => $username,
        'password' => $password
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Db\Adapter\Adapter'
            => 'Zend\Db\Adapter\AdapterServiceFactory',
        ],
    ],
    'migrations' => [
        'dir' => __DIR__ . '/../../migrations',
        'namespace' => 'Gibson\Migration'
    ]
];