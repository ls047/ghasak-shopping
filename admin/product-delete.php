<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

admin_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    $pdo = Database::pdo();
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
}

header('Location: index.php');
exit;
