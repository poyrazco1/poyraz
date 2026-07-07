<?php
/**
 * D4stattoo — Site Yapılandırması
 *
 * Plesk'e kurulumdan sonra SADECE bu dosyayı düzenlemeniz yeterlidir.
 * Değiştirilmesi gerekenler: DB_HOST, DB_NAME, DB_USER, DB_PASS ve APP_URL.
 */

// ---------------------------------------------------------------------------
// VERİTABANI (Plesk > Veritabanları ekranındaki bilgilerle doldurun)
// ---------------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'd4stattoo');
define('DB_USER', 'd4user');
define('DB_PASS', 'd4pass');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// SİTE
// ---------------------------------------------------------------------------
// Sitenin tam adresi, sonda / OLMADAN.
// Boş bırakılırsa istekten otomatik algılanır (Plesk'te genelde sorunsuz çalışır).
define('APP_URL', 'https://d4stattoo.com');

define('SITE_NAME', 'D4S Tattoo');
define('DEFAULT_LANGUAGE', 'tr');

// ---------------------------------------------------------------------------
// YÖNETİCİ (acil durum / kurtarma girişi)
// ---------------------------------------------------------------------------
// Normal yönetici hesapları veritabanındaki `admins` tablosunda tutulur ve
// şifreler password_hash() ile saklanır. Aşağıdaki hesap, veritabanındaki
// hesaba erişilemediğinde dahi çalışan yedek giriştir.
// Yeni hash üretmek için: php -r "echo password_hash('yeni-sifre', PASSWORD_BCRYPT);"
define('ADMIN_EMAIL', 'hasanacar6161@gmail.com');
define('ADMIN_PASSWORD_HASH', '$2y$12$hCy.crRBxKDX2x9bVw4AcOUtHGHCXscSHmbMlPDCVAs.eGXEgIfTq');

// ---------------------------------------------------------------------------
// İLETİŞİM
// ---------------------------------------------------------------------------
define('WHATSAPP_NUMBER', '905058016126');           // Uluslararası format, + ve boşluk olmadan
define('INSTAGRAM_URL', 'https://instagram.com/D4stattoo');

// ---------------------------------------------------------------------------
// UPLOAD
// ---------------------------------------------------------------------------
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024); // 5 MB
define('UPLOAD_DIR', __DIR__ . '/assets/uploads');
define('UPLOAD_URL', 'assets/uploads');

// ---------------------------------------------------------------------------
// SİSTEM — normalde dokunmanız gerekmez
// ---------------------------------------------------------------------------
define('BASE_PATH', __DIR__);
define('LOG_DIR', __DIR__ . '/storage/logs');
define('APP_DEBUG', false); // true: hataları ekrana basar (sadece geliştirme için)

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Hata yönetimi: hatalar ekrana değil storage/logs içine yazılır.
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
if (is_dir(LOG_DIR) && is_writable(LOG_DIR)) {
    ini_set('error_log', LOG_DIR . '/php-error.log');
}
