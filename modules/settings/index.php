<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — ana sayfa.
 *
 * Şu an kullanıcı ve rol yönetimine erişim sağlar. İleride yeni ayar
 * bölümleri bu sayfaya kart olarak eklenebilir.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';

require_permission('settings.view');

// Küçük özetler.
$userCount = 0;
$roleCount = 0;
try {
    $userCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $roleCount = (int) db()->query('SELECT COUNT(*) FROM roles')->fetchColumn();
} catch (Throwable $e) {
    app_log('Ayarlar sayaç hatası: ' . $e->getMessage(), 'WARN');
}

$page_title = 'Ayarlar';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Ayarlar</h2>
        <p class="page-head__subtitle">Kullanıcı ve rol yönetimini buradan yapabilirsiniz.</p>
    </div>
</section>

<div class="grid grid--2">
    <div class="card settings-card">
        <div class="card__body">
            <h3 class="settings-card__title">Kullanıcı Yönetimi</h3>
            <p class="settings-card__text">Kullanıcıları ekleyin, düzenleyin, rol atayın veya pasife alın.</p>
            <div class="settings-card__meta"><?= e((string) $userCount) ?> kullanıcı</div>
            <a class="btn btn--primary" href="<?= e(url('modules/settings/users.php')) ?>">Kullanıcıları Yönet</a>
        </div>
    </div>

    <div class="card settings-card">
        <div class="card__body">
            <h3 class="settings-card__title">Rol Yönetimi</h3>
            <p class="settings-card__text">Rolleri oluşturun, düzenleyin ve yetki altyapısını yönetin.</p>
            <div class="settings-card__meta"><?= e((string) $roleCount) ?> rol</div>
            <a class="btn btn--primary" href="<?= e(url('modules/settings/roles.php')) ?>">Rolleri Yönet</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
