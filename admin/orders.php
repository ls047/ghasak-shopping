<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

admin_require_login();

$pdo = Database::pdo();
$orders = $pdo->query(
    'SELECT id, customer_name, email, total, created_at FROM orders ORDER BY id DESC'
)->fetchAll();

$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$items = [];
if ($orderId > 0) {
    $st = $pdo->prepare(
        "SELECT oi.*, COALESCE(p.name, 'Product #' || oi.product_id) AS product_name
         FROM order_items oi
         LEFT JOIN products p ON p.id = oi.product_id
         WHERE oi.order_id = ?
         ORDER BY oi.id"
    );
    $st->execute([$orderId]);
    $items = $st->fetchAll();
}

$orderRows = '';
foreach ($orders as $o) {
    $orderRows .= template('admin/order-row.html', [
        'row_class' => $orderId === (int) $o['id'] ? 'row-highlight' : '',
        'id' => (string) (int) $o['id'],
        'customer_name' => h((string) $o['customer_name']),
        'email' => h((string) $o['email']),
        'total' => h(money_format_shop((float) $o['total'])),
        'created_at' => h((string) $o['created_at']),
    ]);
}

$emptyOrders = '';
if (!$orders) {
    $emptyOrders = '<p class="muted"><i class="fa-solid fa-inbox"></i> No orders yet.</p>';
}

$orderDetail = '';
if ($orderId && $items) {
    $itemRows = '';
    foreach ($items as $i) {
        $line = (float) $i['price_each'] * (int) $i['quantity'];
        $itemRows .= template('admin/order-item-row.html', [
            'product_name' => h((string) $i['product_name']),
            'quantity' => (string) (int) $i['quantity'],
            'price_each' => h(money_format_shop((float) $i['price_each'])),
            'line_total' => h(money_format_shop($line)),
        ]);
    }
    $orderDetail = template('admin/order-detail.html', [
        'order_id' => (string) $orderId,
        'item_rows' => $itemRows,
    ]);
} elseif ($orderId) {
    $orderDetail = '<p class="muted"><i class="fa-solid fa-circle-question"></i> Order not found.</p>';
}

admin_header('Orders');
echo template('admin/page-orders.html', [
    'order_rows' => $orderRows,
    'empty_orders' => $emptyOrders,
    'order_detail' => $orderDetail,
]);
admin_footer();
