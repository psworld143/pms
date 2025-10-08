<?php
// CLI helper to run booking API scripts with a fake manager session

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'manager';

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_SCHEME'] = 'http';
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);

$script = $argv[1] ?? null;
if (!$script) {
    fwrite(STDERR, "Usage: php cli_api_runner.php <api-script.php> [query]");
    exit(1);
}

$apiDir = realpath(__DIR__ . '/../api');
if ($apiDir === false) {
    fwrite(STDERR, "Unable to locate API directory\n");
    exit(1);
}

chdir($apiDir);

parse_str($argv[2] ?? '', $queryParams);
$_GET = $queryParams;

require $script;
