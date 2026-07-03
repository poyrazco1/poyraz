<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — rol silme.
 *
 * Yalnızca POST + CSRF ile çalışır. Yönetici rolü ve kullanıcıya atanmış roller
 * silinemez.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('roles.manage');

if (!is_post()) {
    flash('error', 'Geçersiz istek.');
    redirect('/modules/settings/roles.php');
}

csrf_require('/modules/settings/roles.php');

$roleId = (int) input('role_id');
if ($roleId <= 0) {
    flash('error', 'Geçersiz rol.');
    redirect('/modules/settings/roles.php');
}

$stmt = db()->prepare('SELECT id, slug FROM roles WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $roleId]);
$role = $stmt->fetch();

if ($role === false) {
    flash('error', 'Rol bulunamadı.');
    redirect('/modules/settings/roles.php');
}

// Yönetici rolü silinemez.
if ($role['slug'] === 'yonetici') {
    flash('error', 'Yönetici rolü sistem için kritiktir ve silinemez.');
    redirect('/modules/settings/roles.php');
}

// Bu role atanmış kullanıcı varsa silme engellenir.
$count = db()->prepare('SELECT COUNT(*) FROM users WHERE role_id = :id');
$count->execute([':id' => $roleId]);
if ((int) $count->fetchColumn() > 0) {
    flash('error', 'Bu role atanmış kullanıcılar var. Önce kullanıcıların rolünü değiştirin.');
    redirect('/modules/settings/roles.php');
}

$del = db()->prepare('DELETE FROM roles WHERE id = :id');
$del->execute([':id' => $roleId]);

flash('success', 'Rol silindi.');
redirect('/modules/settings/roles.php');
