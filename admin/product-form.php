<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/admin_auth.php';

admin_require_login();

$pdo = Database::pdo();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = [
    'name' => '',
    'description' => '',
    'price' => '',
    'stock' => 0,
    'image_url' => '',
    'active' => 1,
];

if ($id > 0) {
    $st = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    $found = $st->fetch();
    if ($found) {
        $row = $found;
    } else {
        $id = 0;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $row['name'] = trim((string) ($_POST['name'] ?? ''));
    $row['description'] = trim((string) ($_POST['description'] ?? ''));
    $row['price'] = (string) ($_POST['price'] ?? '0');
    $row['stock'] = (int) ($_POST['stock'] ?? 0);
    $row['image_url'] = trim((string) ($_POST['image_url'] ?? ''));
    $row['active'] = isset($_POST['active']) ? 1 : 0;

    if ($row['name'] === '') {
        $errors[] = 'Name is required.';
    }
    $priceVal = filter_var($row['price'], FILTER_VALIDATE_FLOAT);
    if ($priceVal === false || $priceVal < 0) {
        $errors[] = 'Price must be a non-negative number.';
    }
    if ($row['stock'] < 0) {
        $errors[] = 'Stock cannot be negative.';
    }

    if (!$errors) {
        if ($id > 0) {
            $upd = $pdo->prepare(
                'UPDATE products SET name=?, description=?, price=?, stock=?, image_url=?, active=? WHERE id=?'
            );
            $upd->execute([
                $row['name'],
                $row['description'],
                $priceVal,
                $row['stock'],
                $row['image_url'] ?: null,
                $row['active'],
                $id,
            ]);
        } else {
            $ins = $pdo->prepare(
                'INSERT INTO products (name, description, price, stock, image_url, active) VALUES (?,?,?,?,?,?)'
            );
            $ins->execute([
                $row['name'],
                $row['description'],
                $priceVal,
                $row['stock'],
                $row['image_url'] ?: null,
                $row['active'],
            ]);
        }
        header('Location: index.php');
        exit;
    }
}

$errorBlocks = '';
foreach ($errors as $e) {
    $errorBlocks .= '<p class="notice notice-error"><i class="fa-solid fa-circle-exclamation"></i> ' . h($e) . '</p>';
}

admin_header($id ? 'Edit product' : 'New product');
echo template('admin/page-product-form.html', [
    'form_heading' => $id
        ? '<i class="fa-solid fa-pen-to-square"></i> Edit product'
        : '<i class="fa-solid fa-plus"></i> Add product',
    'error_blocks' => $errorBlocks,
    'name' => h((string) $row['name']),
    'description' => h((string) $row['description']),
    'price' => h((string) $row['price']),
    'stock' => (string) (int) $row['stock'],
    'image_url' => h((string) ($row['image_url'] ?? '')),
    'active_checked' => (int) $row['active'] ? 'checked' : '',
]);
admin_footer();
