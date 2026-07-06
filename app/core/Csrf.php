<?php
/**
 * CSRF koruması. Oturum başına tek token; hash_equals ile doğrulanır.
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Form içine gömülecek gizli input */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function verify(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** POST isteğinde token geçersizse isteği durdurur. */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && !self::verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            die('Oturum doğrulaması başarısız (CSRF). Lütfen sayfayı yenileyip tekrar deneyin.');
        }
    }
}
