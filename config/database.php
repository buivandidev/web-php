<?php
// config/database.php
return [
    'driver'   => 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? 'localhost',
    'port'     => $_ENV['DB_PORT']     ?? '3306',
    'dbname'   => $_ENV['DB_NAME']     ?? 'cinema_db',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4',
];

