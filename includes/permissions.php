<?php

declare(strict_types=1);

/**
 * Yetki (permission) sistemi.
 *
 * Şimdilik basit rol bazlı bir yapı kullanılır. İleride modül bazlı ince
 * yetkilendirmeye genişletilebilecek şekilde tasarlanmıştır. Yetkiler
 * "yetenek (capability)" olarak tanımlanır ve rol slug'larına eşlenir.
 *
 * Yeni bir modül eklerken:
 *   1) Aşağıdaki PERMISSIONS haritasına yeni yetenekler ekleyin.
 *   2) İlgili rollere bu yetenekleri tanıyın.
 *   3) Sayfa başında require_permission('...') çağırın.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

/**
 * Sistemdeki tüm yetenekler ve her yeteneğe erişebilen rol slug'ları.
 *
 * "yonetici" rolü koddan bağımsız olarak her zaman tam yetkilidir
 * (bkz. user_can). Diğer roller burada açıkça tanımlanır.
 *
 * @return array<string, array<int, string>>
 */
function permission_map(): array
{
    return [
        // Ayarlar ve yönetim
        'settings.view'   => ['yonetici'],
        'users.manage'    => ['yonetici'],
        'roles.manage'    => ['yonetici'],

        // Panel geneli (tüm oturum açmış personel erişebilir)
        'dashboard.view'  => ['yonetici', 'muhasebe', 'satis', 'depo', 'personel'],
        'currency.view'   => ['yonetici', 'muhasebe', 'satis', 'depo', 'personel'],
    ];
}

/**
 * Verilen rol slug'ının bir yeteneğe sahip olup olmadığını döndürür.
 *
 * "yonetici" rolü her yeteneğe sahiptir.
 */
function role_can(?string $roleSlug, string $capability): bool
{
    if ($roleSlug === null || $roleSlug === '') {
        return false;
    }
    if ($roleSlug === 'yonetici') {
        return true;
    }
    $map = permission_map();
    if (!isset($map[$capability])) {
        return false;
    }
    return in_array($roleSlug, $map[$capability], true);
}

/**
 * Geçerli oturum açmış kullanıcının bir yeteneğe sahip olup olmadığı.
 */
function user_can(string $capability): bool
{
    $user = current_user();
    if ($user === null) {
        return false;
    }
    return role_can($user['role_slug'] ?? null, $capability);
}

/**
 * Belirtilen yetenek yoksa erişimi engeller.
 *
 * Oturum yoksa login'e, yetki yoksa 403 ile dashboard'a yönlendirir.
 */
function require_permission(string $capability): void
{
    require_login();

    if (!user_can($capability)) {
        flash('error', 'Bu sayfaya erişim yetkiniz bulunmuyor.');
        http_response_code(403);
        redirect('/dashboard.php');
    }
}
