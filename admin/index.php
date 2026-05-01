<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

admin_require_login();

$pdo = Database::pdo();
$products = $pdo->query(
    'SELECT id, name, price, stock, active, created_at FROM products ORDER BY id DESC'
)->fetchAll();

$productRows = '';
foreach ($products as $p) {
    $activeLabel = (int) $p['active']
        ? '<span class="pill pill-on"><i class="fa-solid fa-eye"></i> Yes</span>'
        : '<span class="pill pill-off"><i class="fa-solid fa-eye-slash"></i> No</span>';
    $productRows .= template('admin/product-row.html', [
        'id' => (string) (int) $p['id'],
        'name' => h((string) $p['name']),
        'price' => h(money_format_shop((float) $p['price'])),
        'stock' => (string) (int) $p['stock'],
        'active_label' => $activeLabel,
    ]);
}

$emptyProducts = '';
if (!$products) {
    $emptyProducts = '<p class="muted"><i class="fa-solid fa-inbox"></i> No products yet. Add one to get started.</p>';
}

admin_header('Products');
echo template('admin/page-products.html', [
    'product_rows' => $productRows,
    'empty_products' => $emptyProducts,
]);
admin_footer();
