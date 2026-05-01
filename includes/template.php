<?php
declare(strict_types=1);

/**
 * Load an HTML file from /templates and replace {{placeholders}}.
 * Escape all dynamic text in PHP with h() before passing values here.
 */
function template(string $relativePath, array $vars = []): string
{
    $path = ROOT_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_readable($path)) {
        throw new RuntimeException('Template not found: ' . $relativePath);
    }
    $html = file_get_contents($path);
    if ($html === false) {
        return '';
    }
    foreach ($vars as $key => $value) {
        $html = str_replace('{{' . $key . '}}', (string) $value, $html);
    }
    return $html;
}

function shop_header(string $pageTitle): void
{
    echo template('shop/header.html', [
        'page_title' => h($pageTitle),
        'base_path' => '.',
        'cart_count' => (string) cart_count(),
    ]);
}

function shop_footer(): void
{
    echo template('shop/footer.html', [
        'base_path' => '.',
    ]);
}

function admin_header(string $pageTitle): void
{
    echo template('admin/header.html', [
        'page_title' => h($pageTitle),
        'root_path' => '..',
    ]);
}

function admin_footer(): void
{
    echo template('admin/footer.html', [
        'root_path' => '..',
    ]);
}
