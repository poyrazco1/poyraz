<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — rol listesi.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('roles.manage');

// Rolleri, atanmış kullanıcı sayısıyla birlikte listele.
$roles = db()->query(
    'SELECT r.id, r.name, r.slug, r.description,
            (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS user_count
     FROM roles r
     ORDER BY r.id ASC'
)->fetchAll();

$page_title = 'Roller';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Roller</h2>
        <p class="page-head__subtitle">Kullanıcı rollerini yönetin.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/index.php')) ?>">Ayarlar</a>
        <a class="btn btn--primary" href="<?= e(url('modules/settings/role-create.php')) ?>">Yeni Rol</a>
    </div>
</section>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Slug</th>
                    <th>Açıklama</th>
                    <th>Kullanıcı</th>
                    <th class="table__actions-col">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr><td colspan="5" class="table__empty">Henüz rol yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($roles as $r): ?>
                        <?php $isAdminRole = ($r['slug'] === 'yonetici'); ?>
                        <tr>
                            <td data-label="Rol"><?= e((string) $r['name']) ?></td>
                            <td data-label="Slug"><code class="code-inline"><?= e((string) $r['slug']) ?></code></td>
                            <td data-label="Açıklama" class="table__cell--truncate"><?= e((string) ($r['description'] ?? '')) ?></td>
                            <td data-label="Kullanıcı"><?= (int) $r['user_count'] ?></td>
                            <td data-label="İşlemler">
                                <div class="row-actions">
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('modules/settings/role-edit.php?id=' . (int) $r['id'])) ?>">Düzenle</a>

                                    <?php if ($isAdminRole): ?>
                                        <span class="row-actions__self">Sistem rolü</span>
                                    <?php else: ?>
                                        <form method="post" action="<?= e(url('modules/settings/role-delete.php')) ?>"
                                              class="inline-form" data-confirm="Bu rolü silmek istediğinize emin misiniz?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="role_id" value="<?= (int) $r['id'] ?>">
                                            <button type="submit" class="btn btn--sm btn--danger" <?= (int) $r['user_count'] > 0 ? 'disabled title="Bu role atanmış kullanıcılar var"' : '' ?>>Sil</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
