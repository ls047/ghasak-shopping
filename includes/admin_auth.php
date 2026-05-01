<?php
declare(strict_types=1);

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_require_login(): void
{
    if (!admin_logged_in()) {
        redirect('admin/login.php');
    }
}

function admin_login(int $id, string $username): void
{
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_user'] = $username;
}

function admin_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_user']);
}
