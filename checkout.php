<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/cart.php';

$pdo = Database::pdo();
$lines = cart_get_items();
$items = [];
$subtotal = 0.0;

foreach ($lines as $line) {
    $st = $pdo->prepare(
        'SELECT id, name, price, stock, active FROM products WHERE id = ?'
    );
    $st->execute([$line['product_id']]);
    $p = $st->fetch();
    if (!$p || !(int) $p['active']) {
        continue;
    }
    $qty = min($line['quantity'], (int) $p['stock']);
    if ($qty <= 0) {
        continue;
    }
    $price = (float) $p['price'];
    $lineTotal = $price * $qty;
    $subtotal += $lineTotal;
    $items[] = [
        'id' => (int) $p['id'],
        'name' => $p['name'],
        'price' => $price,
        'quantity' => $qty,
        'stock' => (int) $p['stock'],
    ];
}

$error = null;
$orderId = null;

if (!$items) {
    redirect('cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['customer_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter your name and a valid email.';
    } else {
        try {
            $pdo->beginTransaction();
            $insO = $pdo->prepare(
                'INSERT INTO orders (customer_name, email, total) VALUES (?, ?, ?)'
            );
            $insO->execute([$name, $email, $subtotal]);
            $orderId = (int) $pdo->lastInsertId();
            $insI = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, price_each) VALUES (?, ?, ?, ?)'
            );
            $upd = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

            foreach ($items as $row) {
                $insI->execute([
                    $orderId,
                    $row['id'],
                    $row['quantity'],
                    $row['price'],
                ]);
                $upd->execute([$row['quantity'], $row['id'], $row['quantity']]);
                if ($upd->rowCount() !== 1) {
                    throw new RuntimeException('Stock changed; please refresh cart.');
                }
            }
            $pdo->commit();
            cart_clear();
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
            $orderId = null;
        }
    }
}

if ($orderId) {
    $checkoutContent = template('shop/checkout-success.html', [
        'order_id' => (string) $orderId,
    ]);
} else {
    $checkoutContent = template('shop/checkout-form.html', [
        'error_block' => $error
            ? '<p class="notice notice-error"><i class="fa-solid fa-triangle-exclamation"></i> ' . h($error) . '</p>'
            : '',
        'subtotal' => h(money_format_shop($subtotal)),
        'customer_name' => h((string) ($_POST['customer_name'] ?? '')),
        'email' => h((string) ($_POST['email'] ?? '')),
    ]);
}

shop_header('Checkout');
echo template('shop/page-checkout.html', ['checkout_content' => $checkoutContent]);
shop_footer();
