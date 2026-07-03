<?php

declare(strict_types=1);

/**
 * CSRF (Cross-Site Request Forgery) koruması.
 *
 * Her oturum için bir token üretilir. Formlarda gizli alan olarak eklenir
 * ve POST işlemlerinde doğrulanır.
 */

require_once __DIR__ . '/helpers.php';

/**
 * Geçerli oturum için CSRF token'ını döndürür; yoksa üretir.
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Formlara eklenecek gizli CSRF alanını HTML olarak döndürür.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Gönderilen token'ı sabit zamanlı olarak doğrular.
 */
function csrf_verify(?string $token): bool
{
    $expected = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($token) || $token === '' || !is_string($expected) || $expected === '') {
        return false;
    }
    return hash_equals($expected, $token);
}

/**
 * POST isteklerinde CSRF token'ını zorunlu kılar.
 *
 * Geçersizse flash mesaj bırakır ve verilen yola yönlendirir.
 */
function csrf_require(string $redirectPath): void
{
    if (!is_post()) {
        return;
    }
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_verify(is_string($token) ? $token : null)) {
        flash('error', 'Oturum doğrulaması başarısız oldu. Lütfen tekrar deneyin.');
        redirect($redirectPath);
    }
}
