<?php
// migrations/run.php
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');

// Load environment variables from .env if it exists
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (strpos($line, '=') === false) continue;
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

require_once ROOT_PATH . '/app/Core/Database.php';

use App\Core\Database;

// Custom autoloader to load Database class
spl_autoload_register(function (string $class) {
    if (str_starts_with($class, 'App\\')) {
        $relativeClass = substr($class, 4);
        $path = ROOT_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

try {
    $pdo = Database::getInstance();
    echo "Connected to the database successfully.\n";

    $files = [
        '001_create_users.sql',
        '002_create_movies.sql',
        '003_create_rooms.sql',
        '004_create_showtimes.sql',
        '005_create_tickets.sql',
        '006_create_promotions.sql',
        '007_seed_admin_user.sql',
        '008_seed_rooms.sql',
        '009_seed_data.sql'
    ];

    foreach ($files as $file) {
        $filePath = __DIR__ . '/' . $file;
        if (!file_exists($filePath)) {
            echo "Warning: File $file not found.\n";
            continue;
        }
        echo "Running migration: $file...\n";
        $sql = file_get_contents($filePath);
        $pdo->exec($sql);
        echo "Successfully run $file.\n";
    }

    echo "All migrations completed successfully!\n";
} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
    exit(1);
}
