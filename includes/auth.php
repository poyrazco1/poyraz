<?php

declare(strict_types=1);

/**
 * Kimlik doğrulama (authentication) işlemleri.
 *
 * Giriş / çıkış, "beni hatırla" çerezi, oturum kontrolü ve giriş kayıtları
 * bu dosyada yönetilir. Sayfalar require_login() ile korunur.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

/**
 * Geçerli oturum açmış kullanıcıyı (rol bilgisiyle birlikte) döndürür.
 * Kullanıcı yoksa null döner. İstek başına tek kez sorgular.
 *
 * @return array<string, mixed>|null
 */
function current_user(): ?array
{
    static $cached = false;
    static $user = null;

    if ($cached) {
        return $user;
    }
    $cached = true;

    // Önce oturumdaki kullanıcı, yoksa "beni hatırla" çerezini dene.
    $userId = $_SESSION['user_id'] ?? null;
    if ($userId === null) {
        $userId = attempt_remember_login();
    }
    if ($userId === null) {
        return $user = null;
    }

    $stmt = db()->prepare(
        'SELECT u.*, r.name AS role_name, r.slug AS role_slug
         FROM users u
         LEFT JOIN roles r ON r.id = u.role_id
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute([':id' => (int) $userId]);
    $row = $stmt->fetch();

    if ($row === false || (int) ($row['status'] ?? 0) !== 1) {
        // Pasif ya da silinmiş kullanıcı: oturumu düşür.
        logout_user();
        return $user = null;
    }

    return $user = $row;
}

/**
 * Oturum açılmış mı?
 */
function is_logged_in(): bool
{
    return current_user() !== null;
}

/**
 * Sayfayı korur: oturum yoksa login'e yönlendirir.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Devam etmek için lütfen giriş yapın.');
        redirect('/login.php');
    }
}

/**
 * Kullanıcıyı oturuma yerleştirir (session sabitleme saldırısına karşı
 * oturum kimliğini yeniler) ve isteğe bağlı "beni hatırla" çerezi kurar.
 *
 * @param array<string, mixed> $userRow
 */
function login_user(array $userRow, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $userRow['id'];

    $stmt = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $stmt->execute([':id' => (int) $userRow['id']]);

    if ($remember) {
        set_remember_cookie((int) $userRow['id']);
    } else {
        clear_remember_cookie((int) $userRow['id']);
    }
}

/**
 * Oturumu kapatır: kimlik verisini ve "beni hatırla" çerezini/token'ını temizler.
 *
 * Oturum, çıkış sonrası bilgilendirme (flash) mesajı taşınabilsin diye yok
 * edilmez; bunun yerine tüm veriler temizlenip oturum kimliği yenilenir. Böylece
 * hem oturum sabitleme (session fixation) hem de yetkisiz erişim engellenir.
 */
function logout_user(): void
{
    $userId = $_SESSION['user_id'] ?? null;

    // "Beni hatırla" token'ını hem çerezden hem veritabanından temizle.
    clear_remember_cookie($userId !== null ? (int) $userId : null);

    // Oturum verilerini tamamen boşalt.
    $_SESSION = [];

    // Yeni ve boş bir oturum kimliği üret (fixation koruması).
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Kullanıcı adı veya e-posta + şifre ile giriş dener.
 *
 * @return array{success: bool, message: string}
 */
function attempt_login(string $identifier, string $password, bool $remember = false): array
{
    $identifier = trim($identifier);

    if ($identifier === '' || $password === '') {
        record_login_attempt(null, false, 'Eksik bilgi');
        return ['success' => false, 'message' => 'Lütfen kullanıcı adı/e-posta ve şifre girin.'];
    }

    $stmt = db()->prepare(
        'SELECT * FROM users WHERE username = :id OR email = :id LIMIT 1'
    );
    $stmt->execute([':id' => $identifier]);
    $user = $stmt->fetch();

    if ($user === false) {
        record_login_attempt(null, false, 'Kullanıcı bulunamadı: ' . $identifier);
        return ['success' => false, 'message' => 'Kullanıcı adı/e-posta veya şifre hatalı.'];
    }

    if ((int) ($user['status'] ?? 0) !== 1) {
        record_login_attempt((int) $user['id'], false, 'Pasif hesap');
        return ['success' => false, 'message' => 'Hesabınız pasif durumda. Lütfen yöneticinizle iletişime geçin.'];
    }

    if (!password_verify($password, (string) $user['password_hash'])) {
        record_login_attempt((int) $user['id'], false, 'Hatalı şifre');
        return ['success' => false, 'message' => 'Kullanıcı adı/e-posta veya şifre hatalı.'];
    }

    // Şifre algoritması eskiyse yeniden hashle.
    if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = db()->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
        $upd->execute([':h' => $newHash, ':id' => (int) $user['id']]);
    }

    login_user($user, $remember);
    record_login_attempt((int) $user['id'], true, 'Başarılı giriş');

    return ['success' => true, 'message' => 'Giriş başarılı.'];
}

/**
 * Giriş denemesini login_logs tablosuna kaydeder (temel brute-force altyapısı).
 */
function record_login_attempt(?int $userId, bool $success, string $message): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO login_logs (user_id, ip_address, user_agent, success, message, created_at)
             VALUES (:user_id, :ip, :ua, :success, :message, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':ip'      => client_ip(),
            ':ua'      => client_user_agent(),
            ':success' => $success ? 1 : 0,
            ':message' => mb_substr($message, 0, 255),
        ]);
    } catch (Throwable $e) {
        app_log('login_logs yazılamadı: ' . $e->getMessage(), 'WARN');
    }
}

/* ---------------------------------------------------------------------------
 * "Beni hatırla" çerez sistemi
 * ------------------------------------------------------------------------- */

/**
 * Güvenli "beni hatırla" çerezi oluşturur. Çerezde "userId:rawToken"
 * tutulur; veritabanında token'ın SHA-256 özeti saklanır.
 */
function set_remember_cookie(int $userId): void
{
    $raw = bin2hex(random_bytes(32));
    $hash = hash('sha256', $raw);

    $stmt = db()->prepare('UPDATE users SET remember_token = :t WHERE id = :id');
    $stmt->execute([':t' => $hash, ':id' => $userId]);

    $days = (int) (defined('REMEMBER_COOKIE_DAYS') ? REMEMBER_COOKIE_DAYS : 30);
    $expires = time() + ($days * 86400);

    setcookie(
        REMEMBER_COOKIE_NAME,
        $userId . ':' . $raw,
        [
            'expires'  => $expires,
            'path'     => '/',
            'domain'   => '',
            'secure'   => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * "Beni hatırla" çerezini ve (kullanıcı biliniyorsa) DB token'ını temizler.
 */
function clear_remember_cookie(?int $userId = null): void
{
    if ($userId !== null) {
        try {
            $stmt = db()->prepare('UPDATE users SET remember_token = NULL WHERE id = :id');
            $stmt->execute([':id' => $userId]);
        } catch (Throwable $e) {
            app_log('remember_token temizlenemedi: ' . $e->getMessage(), 'WARN');
        }
    }

    if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
        unset($_COOKIE[REMEMBER_COOKIE_NAME]);
    }
    setcookie(
        REMEMBER_COOKIE_NAME,
        '',
        [
            'expires'  => time() - 42000,
            'path'     => '/',
            'domain'   => '',
            'secure'   => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * "Beni hatırla" çerezini doğrular. Geçerliyse oturumu açar ve kullanıcı
 * kimliğini döndürür; aksi halde null.
 */
function attempt_remember_login(): ?int
{
    $cookie = $_COOKIE[REMEMBER_COOKIE_NAME] ?? null;
    if (!is_string($cookie) || strpos($cookie, ':') === false) {
        return null;
    }

    [$userIdPart, $rawToken] = explode(':', $cookie, 2);
    $userId = (int) $userIdPart;
    if ($userId <= 0 || $rawToken === '') {
        clear_remember_cookie();
        return null;
    }

    $stmt = db()->prepare('SELECT id, remember_token, status FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();

    if ($user === false || empty($user['remember_token']) || (int) $user['status'] !== 1) {
        clear_remember_cookie($userId);
        return null;
    }

    $hash = hash('sha256', $rawToken);
    if (!hash_equals((string) $user['remember_token'], $hash)) {
        // Token uyuşmuyor: olası çalınma; token'ı geçersiz kıl.
        clear_remember_cookie($userId);
        return null;
    }

    // Oturumu aç ve token'ı döndür (rotasyon: yeni token üret).
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    set_remember_cookie($userId);

    $upd = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
    $upd->execute([':id' => $userId]);

    return $userId;
}
