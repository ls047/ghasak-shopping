<?php
declare(strict_types=1);

/** Path from /admin/*.php to project root (always ..) */
$root = $page_prefix ?? '..';
$page_title = $page_title ?? 'Dashboard';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?> — Admin</title>
    <link rel="stylesheet" href="<?= h($root) ?>/assets/css/style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="container admin-header-inner">
            <a class="logo" href="index.php">MiniShop Admin</a>
            <nav class="admin-nav">
                <a href="index.php">Products</a>
                <a href="orders.php">Orders</a>
                <a href="<?= h($root) ?>/index.php" target="_blank" rel="noopener">View store</a>
                <a href="logout.php">Log out</a>
            </nav>
        </div>
    </header>
    <main class="container admin-main">
