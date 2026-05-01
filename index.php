<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/cart.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product_id'])) {
    $pid = (int) $_POST['add_product_id'];
    $qty = max(1, (int) ($_POST['qty'] ?? 1));
    $pdo = Database::pdo();
    $st = $pdo->prepare('SELECT id, stock, active FROM products WHERE id = ?');
    $st->execute([$pid]);
    $p = $st->fetch();
    if ($p && (int) $p['active'] === 1) {
        $stock = (int) $p['stock'];
        $current = 0;
        if (isset($_SESSION['cart'][(string) $pid])) {
            $current = (int) $_SESSION['cart'][(string) $pid];
        }
        $add = min($qty, max(0, $stock - $current));
        if ($add > 0) {
            cart_add($pid, $add);
        }
    }
    redirect('index.php');
}

$pdo = Database::pdo();
$products = $pdo->query(
    'SELECT id, name, description, price, stock, image_url FROM products WHERE active = 1 ORDER BY name'
)->fetchAll();

$productCards = '';
foreach ($products as $p) {
    if (!empty($p['image_url'])) {
        $media = '<img src="' . h((string) $p['image_url']) . '" alt="" class="product-img">';
    } else {
        $letter = h(strtoupper(substr((string) $p['name'], 0, 1)));
        $media = '<div class="product-placeholder" aria-hidden="true"><span class="ph-letter">' . $letter . '</span></div>';
    }

    if ((int) $p['stock'] <= 0) {
        $buyBlock = '<p class="stock-out"><i class="fa-solid fa-circle-xmark"></i> Out of stock</p>';
    } else {
        $buyBlock = template('shop/product-buy-form.html', [
            'id' => (string) (int) $p['id'],
            'max_qty' => (string) (int) $p['stock'],
        ]);
    }

    $productCards .= template('shop/product-card.html', [
        'product_media' => $media,
        'name' => h((string) $p['name']),
        'stock' => (string) (int) $p['stock'],
        'description' => h((string) $p['description']),
        'price' => h(money_format_shop((float) $p['price'])),
        'buy_block' => $buyBlock,
    ]);
}

$emptyCatalog = '';
if (!$products) {
    $emptyCatalog = '<p class="muted empty-catalog"><i class="fa-solid fa-seedling"></i> No products yet. Add some in the admin dashboard.</p>';
}

shop_header('Products');
echo template('shop/page-index.html', [
    'product_cards' => $productCards,
    'empty_catalog' => $emptyCatalog,
]);
shop_footer();
