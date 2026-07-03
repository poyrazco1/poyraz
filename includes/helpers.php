<?php

declare(strict_types=1);

/**
 * Ortak yardımcı fonksiyonlar ve uygulama önyüklemesi (bootstrap).
 *
 * Bu dosya; config.php ve db.php dosyalarını yükler, oturumu başlatır,
 * zaman dilimini ve hata yönetimini kurar. Panel/public tüm giriş
 * noktaları bu dosyayı require eder.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

/* ---------------------------------------------------------------------------
 * Zaman dilimi
 * ------------------------------------------------------------------------- */
if (defined('DEFAULT_TIMEZONE') && DEFAULT_TIMEZONE !== '') {
    date_default_timezone_set(DEFAULT_TIMEZONE);
}

/* ---------------------------------------------------------------------------
 * Hata yönetimi ve loglama
 * ------------------------------------------------------------------------- */
error_reporting(E_ALL);

if (DEBUG) {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

/**
 * Log klasörü yolunu döndürür; yoksa güvenli şekilde oluşturmayı dener.
 */
function logs_dir(): string
{
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

// PHP hatalarını logs/php-error.log içine yaz.
$logDir = logs_dir();
if (is_dir($logDir) && is_writable($logDir)) {
    ini_set('error_log', $logDir . '/php-error.log');
}

/**
 * Uygulama olaylarını / hatalarını log dosyasına yazar.
 */
function app_log(string $message, string $level = 'INFO'): void
{
    $dir = logs_dir();
    if (!is_dir($dir) || !is_writable($dir)) {
        return;
    }
    $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

// İşlenmeyen istisnaları yakala: kullanıcıya çirkin hata gösterme.
set_exception_handler(static function (Throwable $e): void {
    app_log($e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
    http_response_code(500);
    if (DEBUG) {
        echo '<pre style="padding:20px;font-family:monospace;">';
        echo 'Hata: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n";
        echo htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    } else {
        echo '<!doctype html><html lang="tr"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>Beklenmeyen bir hata oluştu</title></head>'
            . '<body style="font-family:system-ui,Arial,sans-serif;background:#F3F4F6;color:#1F2937;'
            . 'display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;">'
            . '<div style="background:#fff;border:1px solid #E2E5EA;border-radius:10px;padding:32px 28px;'
            . 'max-width:420px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,.04);">'
            . '<h1 style="font-size:18px;margin:0 0 8px;">Beklenmeyen bir hata oluştu</h1>'
            . '<p style="color:#6B7280;font-size:14px;margin:0;">İşleminiz tamamlanamadı. '
            . 'Lütfen daha sonra tekrar deneyin.</p></div></body></html>';
    }
    exit;
});

/* ---------------------------------------------------------------------------
 * Oturum (session) yönetimi
 * ------------------------------------------------------------------------- */
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (defined('SESSION_NAME') && SESSION_NAME !== '') {
        session_name(SESSION_NAME);
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------------------------------------------------------------------------
 * Genel yardımcılar
 * ------------------------------------------------------------------------- */

/**
 * XSS önleme için HTML çıktısını escape eder.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * İstek HTTPS üzerinden mi geliyor?
 */
function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (($_SERVER['SERVER_PORT'] ?? '') === '443') {
        return true;
    }
    if (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }
    return false;
}

/**
 * BASE_URL değerini dikkate alarak uygulama içi bağlantı üretir.
 */
function url(string $path = ''): string
{
    $base = rtrim((string) BASE_URL, '/');
    $path = '/' . ltrim($path, '/');
    return $base . $path;
}

/**
 * Verilen yola yönlendirir ve script'i sonlandırır.
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * POST isteği mi?
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * $_POST / $_GET içinden temizlenmiş string değeri döndürür.
 */
function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    if (!is_string($value)) {
        return $default;
    }
    return trim($value);
}

/**
 * İstemci IP adresini döndürür.
 */
function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($ip) ? substr($ip, 0, 45) : '';
}

/**
 * İstemci User-Agent bilgisini döndürür.
 */
function client_user_agent(): string
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return is_string($ua) ? substr($ua, 0, 255) : '';
}

/* ---------------------------------------------------------------------------
 * Flash mesajları (bir sonraki istekte gösterilen tek seferlik mesaj)
 * ------------------------------------------------------------------------- */

/**
 * Flash mesaj ekler. $type: success | error | info | warning
 */
function flash(string $type, string $message): void
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Birikmiş flash mesajlarını döndürür ve temizler.
 *
 * @return array<int, array{type:string, message:string}>
 */
function take_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return is_array($flashes) ? $flashes : [];
}

/* ---------------------------------------------------------------------------
 * Slug üretimi (rol slug'ları vb. için)
 * ------------------------------------------------------------------------- */

/**
 * Türkçe karakterleri de destekleyen basit slug üretici.
 */
function slugify(string $text): string
{
    $map = [
        'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
    ];
    $text = strtr($text, $map);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'rol';
}

/**
 * E-posta biçimini doğrular.
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Tarih değerini okunur biçimde döndürür.
 */
function format_datetime(?string $value): string
{
    if ($value === null || $value === '' || $value === '0000-00-00 00:00:00') {
        return '—';
    }
    $ts = strtotime($value);
    return $ts ? date('d.m.Y H:i', $ts) : '—';
}
