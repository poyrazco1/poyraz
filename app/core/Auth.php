<?php
/**
 * Admin oturum yönetimi.
 * - Şifreler password_hash/password_verify ile doğrulanır.
 * - Başarılı girişte session id yenilenir (fixation koruması).
 * - config.php içindeki ADMIN_EMAIL/ADMIN_PASSWORD_HASH, DB'ye erişilemese
 *   bile çalışan kurtarma hesabıdır.
 * - Basit brute-force koruması: 5 hatalı denemeden sonra 15 dk bekleme.
 */
class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 900;

    public static function attempt(string $email, string $password): bool
    {
        $email = trim(mb_strtolower($email));

        if (self::isLocked()) {
            return false;
        }

        $admin = null;
        try {
            $admin = Database::row(
                'SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1',
                [$email]
            );
        } catch (Throwable $e) {
            app_log('Auth DB hatasi: ' . $e->getMessage());
        }

        // 1) Veritabanı hesabı
        if ($admin && password_verify($password, $admin['password_hash'])) {
            self::login((int) $admin['id'], $admin['name'] ?: 'Admin', $email);
            try {
                Database::run('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
                if (password_needs_rehash($admin['password_hash'], PASSWORD_BCRYPT)) {
                    Database::run('UPDATE admins SET password_hash = ? WHERE id = ?', [
                        password_hash($password, PASSWORD_BCRYPT), $admin['id'],
                    ]);
                }
            } catch (Throwable $e) {
                app_log('Auth last_login guncellenemedi: ' . $e->getMessage());
            }
            return true;
        }

        // 2) Config üzerinden kurtarma hesabı
        if ($email === mb_strtolower(ADMIN_EMAIL) && password_verify($password, ADMIN_PASSWORD_HASH)) {
            self::login(0, 'Yönetici', $email);
            return true;
        }

        self::registerFailure();
        return false;
    }

    private static function login(int $id, string $name, string $email): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id']    = $id;
        $_SESSION['admin_name']  = $name;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_fp']    = self::fingerprint();
        $_SESSION['login_attempts'] = 0;
        unset($_SESSION['login_locked_until']);
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin_id'], $_SESSION['admin_fp'])
            && hash_equals($_SESSION['admin_fp'], self::fingerprint());
    }

    /** Girişsiz erişimde login sayfasına yönlendirir. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . base_url('admin/login.php'));
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function id(): int
    {
        return (int) ($_SESSION['admin_id'] ?? 0);
    }

    public static function name(): string
    {
        return (string) ($_SESSION['admin_name'] ?? 'Admin');
    }

    public static function email(): string
    {
        return (string) ($_SESSION['admin_email'] ?? '');
    }

    // -- brute force -----------------------------------------------------

    public static function isLocked(): bool
    {
        $until = (int) ($_SESSION['login_locked_until'] ?? 0);
        if ($until > time()) {
            return true;
        }
        if ($until !== 0) {
            unset($_SESSION['login_locked_until']);
            $_SESSION['login_attempts'] = 0;
        }
        return false;
    }

    public static function lockRemaining(): int
    {
        return max(0, (int) ($_SESSION['login_locked_until'] ?? 0) - time());
    }

    private static function registerFailure(): void
    {
        $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION['login_locked_until'] = time() + self::LOCK_SECONDS;
        }
        // Zamanlama saldırılarını ve hızlı denemeleri yavaşlat
        usleep(400000);
    }

    private static function fingerprint(): string
    {
        return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|d4st-salt');
    }
}
