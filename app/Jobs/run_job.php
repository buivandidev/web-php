<?php
// app/Jobs/run_job.php

define('ROOT_PATH', dirname(dirname(__DIR__)));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEW_PATH', ROOT_PATH . '/views');

// Custom Autoloader
spl_autoload_register(function (string $class) {
    if (str_starts_with($class, 'App\\')) {
        $relativeClass = substr($class, 4);
        $path = ROOT_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// Load DI Container
$container = require CONFIG_PATH . '/app.php';

// Parse arguments
$jobName = $argv[1] ?? null;

if (!$jobName) {
    echo "Usage: php run_job.php <JobName>\n";
    exit(1);
}

$fullJobName = 'App\\Jobs\\' . $jobName;
if (!class_exists($fullJobName)) {
    echo "Job class $fullJobName not found.\n";
    exit(1);
}

try {
    if ($jobName === 'HoldExpiryJob') {
        $ticketService = $container->make(\App\Models\Services\Interfaces\ITicketService::class);
        $job = new $fullJobName($ticketService);
        $job->run();
    } else {
        echo "Job $jobName not supported in runner.\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "Error running job: " . $e->getMessage() . "\n";
    exit(1);
}
