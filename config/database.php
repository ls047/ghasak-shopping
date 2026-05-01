<?php
declare(strict_types=1);

/**
 * SQLite PDO singleton + schema bootstrap.
 */
final class Database
{
    private static ?\PDO $pdo = null;

    public static function path(): string
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . DIRECTORY_SEPARATOR . 'shop.db';
    }

    public static function pdo(): \PDO
    {
        if (self::$pdo === null) {
            $path = self::path();
            self::$pdo = new \PDO('sqlite:' . $path, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
            self::migrate(self::$pdo);
        }
        return self::$pdo;
    }

    private static function migrate(\PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT,
                price REAL NOT NULL,
                image_url TEXT,
                stock INTEGER NOT NULL DEFAULT 0,
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_name TEXT NOT NULL,
                email TEXT NOT NULL,
                total REAL NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL,
                price_each REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id)
            )'
        );

        $stmt = $pdo->query('SELECT COUNT(*) FROM admins');
        if ($stmt && (int) $stmt->fetchColumn() === 0) {
            $hash = password_hash(
                getenv('SHOP_ADMIN_PASSWORD') ?: 'admin123',
                PASSWORD_DEFAULT
            );
            $ins = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
            $ins->execute(['admin', $hash]);
        }

        $stmt = $pdo->query('SELECT COUNT(*) FROM products');
        if ($stmt && (int) $stmt->fetchColumn() === 0) {
            $samples = [
                ['Wireless Mouse', 'Compact 2.4GHz mouse with silent clicks.', 24.99, 50],
                ['Mechanical Keyboard', 'RGB backlit, hot-swappable switches.', 89.00, 30],
                ['USB-C Hub', '7-in-1 adapter with HDMI and card reader.', 45.50, 100],
            ];
            $ins = $pdo->prepare(
                'INSERT INTO products (name, description, price, stock, active) VALUES (?, ?, ?, ?, 1)'
            );
            foreach ($samples as $row) {
                $ins->execute($row);
            }
        }
    }
}
