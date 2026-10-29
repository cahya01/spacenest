<?php

$databaseConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'sipak',
    'charset' => 'utf8mb4',
];

function getDbConnection()
{
    global $databaseConfig;

    $connection = @new mysqli(
        $databaseConfig['host'],
        $databaseConfig['username'],
        $databaseConfig['password'],
        $databaseConfig['database']
    );

    if ($connection->connect_error) {
        throw new RuntimeException('Koneksi database gagal: ' . $connection->connect_error);
    }

    $connection->set_charset($databaseConfig['charset']);
    return $connection;
}
