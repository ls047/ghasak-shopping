<?php
declare(strict_types=1);

session_start();

require_once dirname(__DIR__) . '/config/database.php';

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') ?: '');
define('ROOT_PATH', dirname(__DIR__));

require_once __DIR__ . '/template.php';

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    if ($path !== '' && $path[0] === '/') {
        header('Location: ' . $path);
    } else {
        header('Location: ' . BASE_URL . '/' . $path);
    }
    exit;
}

function money_format_shop(float $n): string
{
    return '$' . number_format($n, 2);
}
