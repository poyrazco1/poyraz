<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — rol oluşturma.
 *
 * Slug boş bırakılırsa rol adından otomatik üretilir.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('roles.manage');

$errors = [];
$form = ['name' => '', 'slug' => '', 'description' => ''];

if (is_post()) {
    csrf_require('/modules/settings/role-create.php');

    $form['name']        = input('name');
    $form['slug']        = input('slug');
    $form['description'] = input('description');

    if ($form['name'] === '') {
        $errors['name'] = 'Rol adı zorunludur.';
    }

    $slug = $form['slug'] !== '' ? slugify($form['slug']) : slugify($form['name']);

    if (empty($errors)) {
        $chk = db()->prepare('SELECT COUNT(*) FROM roles WHERE slug = :s');
        $chk->execute([':s' => $slug]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['slug'] = 'Bu slug zaten kullanılıyor. Farklı bir slug girin.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare(
            'INSERT INTO roles (name, slug, description, created_at, updated_at)
             VALUES (:name, :slug, :description, NOW(), NOW())'
        );
        $stmt->execute([
            ':name'        => $form['name'],
            ':slug'        => $slug,
            ':description' => $form['description'] !== '' ? $form['description'] : null,
        ]);

        flash('success', 'Rol oluşturuldu.');
        redirect('/modules/settings/roles.php');
    }
}

$page_title = 'Yeni Rol';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Yeni Rol</h2>
        <p class="page-head__subtitle">Yeni bir kullanıcı rolü oluşturun.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/roles.php')) ?>">&larr; Listeye dön</a>
    </div>
</section>

<div class="card card--form">
    <div class="card__body">
        <form method="post" action="<?= e(url('modules/settings/role-create.php')) ?>" novalidate>
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
                           value="<?= e($form['slug']) ?>" placeholder="Boş bırakılırsa otomatik üretilir">
                    <?php if (isset($errors['slug'])): ?><small class="form-error"><?= e($errors['slug']) ?></small><?php else: ?><small class="form-hint">Örn: muhasebe, satis. Boş bırakırsanız addan üretilir.</small><?php endif; ?>
                </div>

                <div class="form-group form-group--full">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= e($form['description']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Rolü Kaydet</button>
                <a class="btn btn--ghost" href="<?= e(url('modules/settings/roles.php')) ?>">Vazgeç</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
