<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — kullanıcı silme.
 *
 * Yalnızca POST + CSRF ile çalışır. GET ile silme yapılamaz.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('users.manage');

// GET ile silmeyi engelle.
if (!is_post()) {
    flash('error', 'Geçersiz istek.');
    redirect('/modules/settings/users.php');
}

csrf_require('/modules/settings/users.php');

$currentUser = current_user();
$targetId = (int) input('user_id');

if ($targetId <= 0) {
    flash('error', 'Geçersiz kullanıcı.');
    redirect('/modules/settings/users.php');
}

if ($targetId === (int) $currentUser['id']) {
    flash('error', 'Kendi hesabınızı silemezsiniz.');
    redirect('/modules/settings/users.php');
}

$stmt = db()->prepare(
    'SELECT u.id, u.status, r.slug AS role_slug FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1'
);
$stmt->execute([':id' => $targetId]);
$target = $stmt->fetch();

if ($target === false) {
    flash('error', 'Kullanıcı bulunamadı.');
    redirect('/modules/settings/users.php');
}

// Son aktif yöneticiyi silmeyi engelle.
if (($target['role_slug'] ?? '') === 'yonetici' && (int) $target['status'] === 1) {
    $activeAdmins = (int) db()->query(
        "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
         WHERE r.slug = 'yonetici' AND u.status = 1"
    )->fetchColumn();
    if ($activeAdmins <= 1) {
        flash('error', 'Son aktif yönetici silinemez.');
        redirect('/modules/settings/users.php');
    }
}

$del = db()->prepare('DELETE FROM users WHERE id = :id');
$del->execute([':id' => $targetId]);

flash('success', 'Kullanıcı silindi.');
redirect('/modules/settings/users.php');
