<?php

declare(strict_types=1);

/**
 * Panel düzeni — üst bar (topbar).
 *
 * Mobilde sidebar aç/kapa düğmesi, sayfa başlığı ve kullanıcı menüsünü içerir.
 */

if (!isset($currentUser)) {
    $currentUser = current_user();
}
if (!isset($pageTitle)) {
    $pageTitle = 'Panel';
}

$displayName = $currentUser['name'] ?? ($currentUser['username'] ?? 'Kullanıcı');
$roleName    = $currentUser['role_name'] ?? '—';
$initial     = mb_strtoupper(mb_substr((string) $displayName, 0, 1, 'UTF-8'), 'UTF-8');
?>
<header class="topbar">
    <div class="topbar__left">
        <button type="button" class="topbar__menu-btn" data-sidebar-toggle aria-label="Menüyü aç/kapat">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
        </button>
        <h1 class="topbar__title"><?= e($pageTitle) ?></h1>
    </div>

    <div class="topbar__right">
        <div class="user-menu" data-user-menu>
            <button type="button" class="user-menu__trigger" data-user-menu-toggle aria-haspopup="true" aria-expanded="false">
                <span class="user-menu__avatar"><?= e($initial) ?></span>
                <span class="user-menu__meta">
                    <span class="user-menu__name"><?= e((string) $displayName) ?></span>
                    <span class="user-menu__role"><?= e((string) $roleName) ?></span>
                </span>
                <svg class="user-menu__caret" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="user-menu__dropdown" data-user-menu-dropdown hidden>
                <div class="user-menu__header">
                    <div class="user-menu__name"><?= e((string) $displayName) ?></div>
                    <div class="user-menu__email"><?= e((string) ($currentUser['email'] ?? '')) ?></div>
                </div>
                <?php if (user_can('settings.view')): ?>
                    <a class="user-menu__item" href="<?= e(url('modules/settings/index.php')) ?>">Ayarlar</a>
                <?php endif; ?>
                <a class="user-menu__item user-menu__item--danger" href="<?= e(url('logout.php')) ?>">Çıkış Yap</a>
            </div>
        </div>
    </div>
</header>
