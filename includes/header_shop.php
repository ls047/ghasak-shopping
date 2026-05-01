<?php
declare(strict_types=1);

$prefix = $page_prefix ?? '.';
$page_title = $page_title ?? 'Shop';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($page_title) ?> — MiniShop</title>
    <link rel="stylesheet" href="<?= h($prefix) ?>/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="logo" href="<?= h($prefix) ?>/index.php">MiniShop</a>
            <nav class="nav">
                <a href="<?= h($prefix) ?>/index.php">Products</a>
                <a href="<?= h($prefix) ?>/cart.php" class="cart-link">Cart <span class="badge" id="cart-badge"><?= cart_count() ?></span></a>
            </nav>
        </div>
    </header>
    <main class="container main-content">
