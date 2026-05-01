<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

if (admin_logged_in()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string) ($_POST['username'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    $pdo = Database::pdo();
    $st = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $st->execute([$user]);
    $row = $st->fetch();
    if ($row && password_verify($pass, $row['password_hash'])) {
        admin_login((int) $row['id'], (string) $row['username']);
        redirect('index.php');
    }
    $error = 'Invalid username or password.';
}

echo template('admin/login.html', [
    'page_title' => h('Admin login'),
    'root_path' => '..',
    'error_block' => $error
        ? '<p class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> ' . h($error) . '</p>'
        : '',
]);
