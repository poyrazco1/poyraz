<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — rol düzenleme.
 *
 * Yönetici rolü sistem için kritiktir; slug'ı değiştirilemez ve korunur.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('roles.manage');

$roleId = (int) input('id');
if ($roleId <= 0) {
    flash('error', 'Geçersiz rol.');
    redirect('/modules/settings/roles.php');
}

$stmt = db()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $roleId]);
$role = $stmt->fetch();

if ($role === false) {
    flash('error', 'Rol bulunamadı.');
    redirect('/modules/settings/roles.php');
}

$isAdminRole = ($role['slug'] === 'yonetici');

$errors = [];
$form = [
    'name'        => (string) $role['name'],
    'slug'        => (string) $role['slug'],
    'description' => (string) ($role['description'] ?? ''),
];

if (is_post()) {
    csrf_require('/modules/settings/role-edit.php?id=' . $roleId);

    $form['name']        = input('name');
    $form['description'] = input('description');
    // Yönetici rolünün slug'ı korunur.
    $form['slug'] = $isAdminRole ? 'yonetici' : input('slug');

    if ($form['name'] === '') {
        $errors['name'] = 'Rol adı zorunludur.';
    }

    $slug = $isAdminRole
        ? 'yonetici'
        : ($form['slug'] !== '' ? slugify($form['slug']) : slugify($form['name']));

    if (empty($errors)) {
        $chk = db()->prepare('SELECT COUNT(*) FROM roles WHERE slug = :s AND id <> :id');
        $chk->execute([':s' => $slug, ':id' => $roleId]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['slug'] = 'Bu slug başka bir rolde kullanılıyor.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare(
            'UPDATE roles SET name = :name, slug = :slug, description = :description, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'        => $form['name'],
            ':slug'        => $slug,
            ':description' => $form['description'] !== '' ? $form['description'] : null,
            ':id'          => $roleId,
        ]);

        flash('success', 'Rol güncellendi.');
        redirect('/modules/settings/roles.php');
    }
}

$page_title = 'Rol Düzenle';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Rol Düzenle</h2>
        <p class="page-head__subtitle"><?= e((string) $role['name']) ?> rolünü düzenleyin.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/roles.php')) ?>">&larr; Listeye dön</a>
    </div>
</section>

<div class="card card--form">
    <div class="card__body">
        <?php if ($isAdminRole): ?>
            <div class="alert alert--info">Bu bir sistem rolüdür. Slug değeri değiştirilemez.</div>
        <?php endif; ?>

        <form method="post" action="<?= e(url('modules/settings/role-edit.php?id=' . $roleId)) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Rol Adı</label>
                    <input type="text" id="name" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                           value="<?= e($form['name']) ?>" required>
                    <?php if (isset($errors['name'])): ?><small class="form-error"><?= e($errors['name']) ?></small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control<?= isset($errors['slug']) ? ' is-invalid' : '' ?>"
                           value="<?= e($form['slug']) ?>" <?= $isAdminRole ? 'readonly' : '' ?>>
                    <?php if (isset($errors['slug'])): ?><small class="form-error"><?= e($errors['slug']) ?></small><?php endif; ?>
                </div>

                <div class="form-group form-group--full">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= e($form['description']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Değişiklikleri Kaydet</button>
                <a class="btn btn--ghost" href="<?= e(url('modules/settings/roles.php')) ?>">Vazgeç</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
