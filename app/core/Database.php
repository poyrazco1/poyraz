<?php
/**
 * PDO tabanlı veritabanı katmanı (singleton).
 * Tüm sorgular prepared statement ile çalışır.
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                app_log('DB baglanti hatasi: ' . $e->getMessage());
                if (APP_DEBUG) {
                    die('Veritabanı bağlantı hatası: ' . e($e->getMessage()));
                }
                http_response_code(500);
                die('Veritabanına bağlanılamadı. Lütfen config.php içindeki DB bilgilerini kontrol edin.');
            }
        }
        return self::$pdo;
    }

    /** SELECT — tüm satırlar */
    public static function all(string $sql, array $params = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** SELECT — tek satır veya null */
    public static function row(string $sql, array $params = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    /** SELECT — tek değer */
    public static function value(string $sql, array $params = [])
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchColumn();
    }

    /** INSERT/UPDATE/DELETE — etkilenen satır sayısı */
    public static function run(string $sql, array $params = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($params);
        return $st->rowCount();
    }

    /** INSERT — yeni kaydın id'si */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $cols) . '`) VALUES ('
              . implode(',', array_fill(0, count($cols), '?')) . ')';
        $st = self::pdo()->prepare($sql);
        $st->execute(array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    /** UPDATE — id üzerinden */
    public static function update(string $table, int $id, array $data): int
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = '`' . $col . '` = ?';
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $params = array_values($data);
        $params[] = $id;
        return self::run($sql, $params);
    }

    /** DELETE — id üzerinden */
    public static function delete(string $table, int $id): int
    {
        return self::run('DELETE FROM `' . $table . '` WHERE id = ?', [$id]);
    }
}
