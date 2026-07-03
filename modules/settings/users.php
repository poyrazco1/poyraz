<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — kullanıcı listesi.
 *
 * Kullanıcıları listeler; ekleme/düzenleme sayfalarına bağlantı verir,
 * aktif/pasif değiştirme ve silme işlemlerini POST + CSRF ile yürütür.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('users.manage');

$currentUser = current_user();

// Aktif/pasif değiştirme (POST + CSRF).
if (is_post() && input('action') === 'toggle_status') {
    csrf_require('/modules/settings/users.php');

    $targetId = (int) input('user_id');
    if ($targetId <= 0) {
        flash('error', 'Geçersiz kullanıcı.');
        redirect('/modules/settings/users.php');
    }

    if ($targetId === (int) $currentUser['id']) {
        flash('error', 'Kendi hesabınızı pasife alamazsınız.');
        redirect('/modules/settings/users.php');
    }

    $stmt = db()->prepare('SELECT u.id, u.status, r.slug AS role_slug FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1');
    $stmt->execute([':id' => $targetId]);
    $target = $stmt->fetch();

    if ($target === false) {
        flash('error', 'Kullanıcı bulunamadı.');
        redirect('/modules/settings/users.php');
    }

    $newStatus = (int) $target['status'] === 1 ? 0 : 1;

    // Son aktif yöneticinin pasife alınmasını engelle.
    if ($newStatus === 0 && ($target['role_slug'] ?? '') === 'yonetici') {
        $activeAdmins = (int) db()->query(
            "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
             WHERE r.slug = 'yonetici' AND u.status = 1"
        )->fetchColumn();
        if ($activeAdmins <= 1) {
            flash('error', 'Sistemde en az bir aktif yönetici bulunmalıdır.');
            redirect('/modules/settings/users.php');
        }
    }

    $upd = db()->prepare('UPDATE users SET status = :s WHERE id = :id');
    $upd->execute([':s' => $newStatus, ':id' => $targetId]);

    flash('success', $newStatus === 1 ? 'Kullanıcı aktif edildi.' : 'Kullanıcı pasife alındı.');
    redirect('/modules/settings/users.php');
}

// Kullanıcı listesi.
$users = db()->query(
    'SELECT u.id, u.name, u.email, u.username, u.status, u.last_login_at, r.name AS role_name
     FROM users u
     LEFT JOIN roles r ON r.id = u.role_id
     ORDER BY u.id ASC'
)->fetchAll();

$page_title = 'Kullanıcılar';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Kullanıcılar</h2>
        <p class="page-head__subtitle">Sistemdeki kullanıcıları yönetin.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/index.php')) ?>">Ayarlar</a>
        <a class="btn btn--primary" href="<?= e(url('modules/settings/user-create.php')) ?>">Yeni Kullanıcı</a>
    </div>
</section>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>Kullanıcı Adı</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Durum</th>
                    <th>Son Giriş</th>
                    <th class="table__actions-col">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" class="table__empty">Henüz kullanıcı yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td data-label="Ad Soyad"><?= e((string) $u['name']) ?></td>
                            <td data-label="Kullanıcı Adı"><?= e((string) $u['username']) ?></td>
                            <td data-label="E-posta" class="table__cell--truncate"><?= e((string) $u['email']) ?></td>
                            <td data-label="Rol"><?= e((string) ($u['role_name'] ?? '—')) ?></td>
                            <td data-label="Durum">
                                <?php if ((int) $u['status'] === 1): ?>
                                    <span class="badge badge--success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge--muted">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Son Giriş"><?= e(format_datetime($u['last_login_at'] ?? null)) ?></td>
                            <td data-label="İşlemler">
                                <div class="row-actions">
                                    <a class="btn btn--sm btn--ghost" href="<?= e(url('modules/settings/user-edit.php?id=' . (int) $u['id'])) ?>">Düzenle</a>

                                    <?php if ((int) $u['id'] !== (int) $currentUser['id']): ?>
                                        <form method="post" action="<?= e(url('modules/settings/users.php')) ?>" class="inline-form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <button type="submit" class="btn btn--sm btn--ghost">
                                                <?= (int) $u['status'] === 1 ? 'Pasife Al' : 'Aktif Et' ?>
                                            </button>
                                        </form>

                                        <form method="post" action="<?= e(url('modules/settings/user-delete.php')) ?>"
                                              class="inline-form" data-confirm="Bu kullanıcıyı silmek istediğinize emin misiniz?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <button type="submit" class="btn btn--sm btn--danger">Sil</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="row-actions__self">Bu sizsiniz</span>
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
