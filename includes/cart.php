<?php
declare(strict_types=1);

/**
 * @return array<int, array{product_id:int, quantity:int}>
 */
function cart_get_items(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return [];
    }
    $out = [];
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int) $pid;
        $qty = (int) $qty;
        if ($pid > 0 && $qty > 0) {
            $out[] = ['product_id' => $pid, 'quantity' => $qty];
        }
    }
    return $out;
}

function cart_count(): int
{
    $n = 0;
    foreach (cart_get_items() as $line) {
        $n += $line['quantity'];
    }
    return $n;
}

function cart_add(int $productId, int $quantity = 1): void
{
    if ($productId <= 0 || $quantity <= 0) {
        return;
    }
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $key = (string) $productId;
    $_SESSION['cart'][$key] = (int) ($_SESSION['cart'][$key] ?? 0) + $quantity;
}

function cart_set(int $productId, int $quantity): void
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $key = (string) $productId;
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$key]);
        return;
    }
    $_SESSION['cart'][$key] = $quantity;
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}
