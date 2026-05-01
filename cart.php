<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['qty'] ?? [] as $pid => $qty) {
            cart_set((int) $pid, (int) $qty);
        }
    } elseif (isset($_POST['clear_cart'])) {
        cart_clear();
    }
    redirect('cart.php');
}

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
        'line_total' => $lineTotal,
    ];
}

$cartBody = '';
if (!$items) {
    $cartBody = template('shop/cart-empty.html', []);
} else {
    $rows = '';
    foreach ($items as $row) {
        $rows .= template('shop/cart-row.html', [
            'id' => (string) $row['id'],
            'name' => h($row['name']),
            'price' => h(money_format_shop($row['price'])),
            'qty' => (string) $row['quantity'],
            'max_stock' => (string) $row['stock'],
            'line_total' => h(money_format_shop($row['line_total'])),
        ]);
    }
    $cartBody = template('shop/cart-filled.html', [
        'cart_rows' => $rows,
        'subtotal' => h(money_format_shop($subtotal)),
    ]);
}

shop_header('Cart');
echo template('shop/page-cart.html', ['cart_body' => $cartBody]);
shop_footer();
