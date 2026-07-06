<?php
/**
 * Hafif otomatik şema tamamlayıcı.
 *
 * Amaç: Yeni özellikler (örn. lokasyon SEO sayfaları) için gereken tablolar,
 * eski bir install.sql ile kurulmuş veritabanında bulunmayabilir. Bu durumda
 * sitenin 500 vermemesi ve özelliğin dosyalar yüklenir yüklenmez çalışması için
 * eksik tablo güvenli biçimde (CREATE TABLE IF NOT EXISTS) oluşturulur ve boşsa
 * demo içerikle doldurulur.
 *
 * Tasarım:
 *  - Yalnızca ilgili sayfalar çağırır (global maliyet yok).
 *  - İstek başına tek kez çalışır (static bayrak).
 *  - Her hata log'lanır ve yutulur; site akışını bozmaz.
 */

/**
 * location_pages tablosunun var ve dolu olduğundan emin olur.
 * @return bool tablo kullanıma hazırsa true
 */
function ensure_location_pages(): bool
{
    static $ready = null;
    if ($ready !== null) {
        return $ready;
    }

    try {
        $pdo = Database::pdo();

        // Tablo var mı?
        $exists = (bool) Database::value(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'location_pages'"
        );

        if (!$exists) {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS `location_pages` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `lang` VARCHAR(5) NOT NULL DEFAULT 'tr',
                  `city` VARCHAR(80) NOT NULL DEFAULT 'İstanbul',
                  `district` VARCHAR(120) NOT NULL,
                  `slug` VARCHAR(190) NOT NULL,
                  `title` VARCHAR(190) NULL,
                  `h1` VARCHAR(190) NULL,
                  `intro` TEXT NULL,
                  `content` MEDIUMTEXT NULL,
                  `neighborhoods` TEXT NULL,
                  `seo_keywords` VARCHAR(400) NULL,
                  `meta_title` VARCHAR(190) NULL,
                  `meta_description` VARCHAR(300) NULL,
                  `canonical_url` VARCHAR(255) NULL,
                  `sort_order` INT NOT NULL DEFAULT 0,
                  `status` TINYINT(1) NOT NULL DEFAULT 1,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uq_loc_slug_lang` (`slug`, `lang`),
                  KEY `ix_loc_lang` (`lang`, `status`, `sort_order`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
            app_log('migrations: location_pages tablosu oluşturuldu.');
        }

        // Boşsa demo içerikle doldur
        $count = (int) Database::value('SELECT COUNT(*) FROM location_pages');
        if ($count === 0) {
            seed_location_pages();
        }

        $ready = true;
    } catch (Throwable $e) {
        app_log('migrations: location_pages hazırlanamadı: ' . $e->getMessage());
        $ready = false;
    }

    return $ready;
}

/** Demo lokasyon kayıtlarını app/data/location_seed.php'den ekler. */
function seed_location_pages(): void
{
    $file = BASE_PATH . '/app/data/location_seed.php';
    if (!is_file($file)) {
        return;
    }
    $rows = require $file;
    if (!is_array($rows) || empty($rows)) {
        return;
    }
    $inserted = 0;
    foreach ($rows as $row) {
        try {
            // INSERT IGNORE benzeri: benzersiz slug çakışırsa atla
            $exists = Database::value(
                'SELECT COUNT(*) FROM location_pages WHERE slug = ? AND lang = ?',
                [$row['slug'], $row['lang'] ?? 'tr']
            );
            if ($exists) {
                continue;
            }
            Database::insert('location_pages', $row);
            $inserted++;
        } catch (Throwable $e) {
            app_log('migrations: lokasyon seed satırı eklenemedi (' . ($row['slug'] ?? '?') . '): ' . $e->getMessage());
        }
    }
    if ($inserted > 0) {
        app_log("migrations: location_pages $inserted demo kayıtla dolduruldu.");
    }
}
