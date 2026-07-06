<?php
/**
 * Genel yardımcı fonksiyonlar.
 */

/** XSS'e karşı HTML çıktısı kaçışlama */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Arayüz çevirisi */
function t(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

/** Uygulama log'u (storage/logs/app.log) */
function app_log(string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents(LOG_DIR . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

/** Sitenin kök URL'i (sonda / yok) */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        if (APP_URL !== '') {
            $base = rtrim(APP_URL, '/');
        } else {
            $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                   || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
            $scheme = $https ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
            // admin/ altından çağrıldığında kökü bul
            if (str_ends_with($dir, '/admin')) {
                $dir = substr($dir, 0, -6);
            }
            $base = $scheme . '://' . $host . $dir;
        }
    }
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

/** Aktif dile göre public URL üretir: url('hizmetler/minimal-tattoo') */
function url(string $path = '', ?string $lang = null): string
{
    $lang = $lang ?? Lang::current();
    $path = trim($path, '/');
    if ($lang !== DEFAULT_LANGUAGE) {
        $path = $lang . ($path === '' ? '' : '/' . $path);
    }
    return base_url($path);
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

/** Upload edilen dosyanın tam URL'i; boşsa placeholder döner */
function upload_url(?string $relative, string $placeholder = 'img/placeholder.svg'): string
{
    if ($relative === null || $relative === '') {
        return asset($placeholder);
    }
    if (preg_match('#^https?://#i', $relative)) {
        return $relative;
    }
    return base_url(ltrim($relative, '/'));
}

function redirect(string $url, int $code = 302): never
{
    header('Location: ' . $url, true, $code);
    exit;
}

// ---------------------------------------------------------------------------
// Ayarlar (settings tablosu) — istek başına tek sorgu ile önbelleğe alınır
// ---------------------------------------------------------------------------

function all_settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (Database::all('SELECT setting_key, setting_value, lang FROM settings') as $row) {
                $cache[$row['lang'] ?: 'tr'][$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            app_log('settings okunamadi: ' . $e->getMessage());
        }
    }
    return $cache;
}

/**
 * Ayar değeri: aktif dil → TR → varsayılan.
 */
function setting(string $key, string $default = '', ?string $lang = null): string
{
    $all  = all_settings();
    $lang = $lang ?? Lang::current();
    return $all[$lang][$key] ?? $all[DEFAULT_LANGUAGE][$key] ?? $default;
}

function set_setting(string $key, string $value, string $lang = 'tr', string $type = 'text'): void
{
    $exists = Database::row(
        'SELECT id FROM settings WHERE setting_key = ? AND lang = ? LIMIT 1', [$key, $lang]
    );
    if ($exists) {
        Database::run('UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE id = ?', [$value, $exists['id']]);
    } else {
        Database::insert('settings', [
            'setting_key' => $key, 'setting_value' => $value,
            'setting_type' => $type, 'lang' => $lang,
        ]);
    }
}

// ---------------------------------------------------------------------------
// Diller
// ---------------------------------------------------------------------------

function active_languages(): array
{
    static $langs = null;
    if ($langs === null) {
        try {
            $langs = Database::all('SELECT * FROM languages WHERE is_active = 1 ORDER BY sort_order');
        } catch (Throwable $e) {
            $langs = [];
        }
        if (empty($langs)) {
            $langs = [['code' => 'tr', 'name' => 'Türkçe', 'direction' => 'ltr', 'is_active' => 1, 'sort_order' => 1]];
        }
    }
    return $langs;
}

function active_language_codes(): array
{
    return array_column(active_languages(), 'code');
}

// ---------------------------------------------------------------------------
// Dil fallback'li içerik sorguları
// ---------------------------------------------------------------------------

/**
 * lang kolonu olan tablodan aktif dil için satırları getirir;
 * o dilde hiç kayıt yoksa TR kayıtlarına düşer.
 */
function rows_lang(string $sql, array $params = [], ?string $lang = null): array
{
    $lang = $lang ?? Lang::current();
    $rows = Database::all($sql, array_merge([$lang], $params));
    if (empty($rows) && $lang !== DEFAULT_LANGUAGE) {
        $rows = Database::all($sql, array_merge([DEFAULT_LANGUAGE], $params));
    }
    return $rows;
}

/** Tek satır; aktif dilde yoksa TR'ye düşer. */
function row_lang(string $sql, array $params = [], ?string $lang = null): ?array
{
    $lang = $lang ?? Lang::current();
    $row = Database::row($sql, array_merge([$lang], $params));
    if ($row === null && $lang !== DEFAULT_LANGUAGE) {
        $row = Database::row($sql, array_merge([DEFAULT_LANGUAGE], $params));
    }
    return $row;
}

// ---------------------------------------------------------------------------
// Flash mesaj + eski form değerleri (PRG deseni)
// ---------------------------------------------------------------------------

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_get(): array
{
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}

function old_set(array $data): void
{
    unset($data['csrf_token']);
    $_SESSION['old_input'] = $data;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old_input'][$key] ?? $default);
}

function old_clear(): void
{
    unset($_SESSION['old_input']);
}

// ---------------------------------------------------------------------------
// WhatsApp
// ---------------------------------------------------------------------------

function whatsapp_number(): string
{
    return preg_replace('/\D/', '', setting('whatsapp_number', WHATSAPP_NUMBER)) ?: WHATSAPP_NUMBER;
}

function whatsapp_link(string $message = ''): string
{
    $url = 'https://wa.me/' . whatsapp_number();
    if ($message !== '') {
        $url .= '?text=' . rawurlencode($message);
    }
    return $url;
}

// ---------------------------------------------------------------------------
// Çeşitli
// ---------------------------------------------------------------------------

/** Türkçe karakter destekli, URL güvenli slug */
function slugify(string $text): string
{
    $map = [
        'ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u',
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'zh',
        'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f',
        'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ы' => 'y',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ь' => '', 'ъ' => '',
    ];
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = strtr($text, $map);
    // Arapça vb. latin dışı karakterler için translit denenir
    if (function_exists('transliterator_transliterate')) {
        $t = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
        if (is_string($t) && $t !== '') {
            $text = $t;
        }
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim(preg_replace('/-+/', '-', $text), '-');
    return $text !== '' ? $text : 'icerik-' . substr(bin2hex(random_bytes(4)), 0, 6);
}

/** Slug tablo içinde benzersiz değilse sonuna -2, -3... ekler */
function unique_slug(string $table, string $slug, string $lang, int $ignoreId = 0): string
{
    $base = $slug;
    $i = 2;
    while (Database::value(
        "SELECT COUNT(*) FROM `$table` WHERE slug = ? AND lang = ? AND id != ?",
        [$slug, $lang, $ignoreId]
    ) > 0) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function excerpt_of(string $text, int $limit = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    return rtrim(mb_substr($text, 0, $limit - 1), " \t.,;:") . '…';
}

function format_date(?string $datetime, bool $withTime = false): string
{
    if (!$datetime) {
        return '-';
    }
    $ts = strtotime($datetime);
    if ($ts === false) {
        return e($datetime);
    }
    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
               'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    if (Lang::current() === 'tr') {
        $out = date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    } else {
        $out = date('j M Y', $ts);
    }
    return $withTime ? $out . ' ' . date('H:i', $ts) : $out;
}

/** Sınırlı HTML'e izin veren temizleyici (admin editör içerikleri için) */
function clean_html(string $html): string
{
    $allowed = '<p><br><b><strong><i><em><u><s><ul><ol><li><a><h2><h3><h4><blockquote><img><table><thead><tbody><tr><th><td><figure><figcaption><span><div><hr>';
    $html = strip_tags($html, $allowed);
    // on* olay öznitelikleri ve javascript: URL'leri temizle
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\2/i', '$1="#"', $html);
    return $html;
}
