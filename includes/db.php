<?php

declare(strict_types=1);

/**
 * Veritabanı bağlantısı.
 *
 * Tüm PDO bağlantıları bu dosya üzerinden yönetilir. config.php içindeki
 * DB_* sabitleri kullanılır. Bağlantı tekil (singleton) olarak tutulur.
 */

require_once __DIR__ . '/../config.php';

/**
 * Uygulama genelinde kullanılan tekil PDO bağlantısını döndürür.
 *
 * @throws PDOException Bağlantı kurulamazsa (çağıran taraf yakalamalıdır).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST
        . ';port=' . DB_PORT
        . ';dbname=' . DB_NAME
        . ';charset=' . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}
