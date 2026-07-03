<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — kullanıcı oluşturma.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('users.manage');

$roles = db()->query('SELECT id, name FROM roles ORDER BY name ASC')->fetchAll();

$errors = [];
$form = [
    'name'     => '',
    'email'    => '',
    'username' => '',
    'role_id'  => '',
    'status'   => '1',
];

if (is_post()) {
    csrf_require('/modules/settings/user-create.php');

    $form['name']     = input('name');
    $form['email']    = input('email');
    $form['username'] = input('username');
    $form['role_id']  = input('role_id');
    $form['status']   = input('status') === '0' ? '0' : '1';
    $password         = (string) ($_POST['password'] ?? '');

    // Doğrulama.
    if ($form['name'] === '') {
        $errors['name'] = 'Ad soyad zorunludur.';
    }
    if ($form['username'] === '') {
        $errors['username'] = 'Kullanıcı adı zorunludur.';
    } elseif (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $form['username'])) {
        $errors['username'] = 'Kullanıcı adı 3-50 karakter olmalı; harf, rakam, . _ - kullanılabilir.';
    }
    if ($form['email'] === '' || !is_valid_email($form['email'])) {
        $errors['email'] = 'Geçerli bir e-posta girin.';
    }
    if ($form['role_id'] === '' || (int) $form['role_id'] <= 0) {
        $errors['role_id'] = 'Lütfen bir rol seçin.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
    }

    // Rol gerçekten var mı?
    if (!isset($errors['role_id'])) {
        $chk = db()->prepare('SELECT COUNT(*) FROM roles WHERE id = :id');
        $chk->execute([':id' => (int) $form['role_id']]);
        if ((int) $chk->fetchColumn() === 0) {
            $errors['role_id'] = 'Seçilen rol geçersiz.';
        }
    }

    // Benzersizlik: e-posta / kullanıcı adı.
    if (!isset($errors['email'])) {
        $chk = db()->prepare('SELECT COUNT(*) FROM users WHERE email = :e');
        $chk->execute([':e' => $form['email']]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['email'] = 'Bu e-posta zaten kullanılıyor.';
        }
    }
    if (!isset($errors['username'])) {
        $chk = db()->prepare('SELECT COUNT(*) FROM users WHERE username = :u');
        $chk->execute([':u' => $form['username']]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['username'] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }
    }

    if (empty($errors)) {
        $stmt = db()->prepare(
            'INSERT INTO users (name, email, username, password_hash, role_id, status, created_at, updated_at)
             VALUES (:name, :email, :username, :hash, :role_id, :status, NOW(), NOW())'
        );
        $stmt->execute([
            ':name'     => $form['name'],
            ':email'    => $form['email'],
            ':username' => $form['username'],
            ':hash'     => password_hash($password, PASSWORD_DEFAULT),
            ':role_id'  => (int) $form['role_id'],
            ':status'   => (int) $form['status'],
        ]);

        flash('success', 'Kullanıcı oluşturuldu.');
        redirect('/modules/settings/users.php');
    }
}

$page_title = 'Yeni Kullanıcı';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Yeni Kullanıcı</h2>
        <p class="page-head__subtitle">Sisteme yeni bir kullanıcı ekleyin.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/users.php')) ?>">&larr; Listeye dön</a>
    </div>
</section>

<div class="card card--form">
    <div class="card__body">
        <form method="post" action="<?= e(url('modules/settings/user-create.php')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Ad Soyad</label>
                    <input type="text" id="name" name="name" class="form-control<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                           value="<?= e($form['name']) ?>" required>
                    <?php if (isset($errors['name'])): ?><small class="form-error"><?= e($errors['name']) ?></small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="username">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" class="form-control<?= isset($errors['username']) ? ' is-invalid' : '' ?>"
                           value="<?= e($form['username']) ?>" required>
                    <?php if (isset($errors['username'])): ?><small class="form-error"><?= e($errors['username']) ?></small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">E-posta</label>
                    <input type="email" id="email" name="email" class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                           value="<?= e($form['email']) ?>" required>
                    <?php if (isset($errors['email'])): ?><small class="form-error"><?= e($errors['email']) ?></small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="role_id">Rol</label>
                    <select id="role_id" name="role_id" class="form-control<?= isset($errors['role_id']) ? ' is-invalid' : '' ?>" required>
                        <option value="">— Seçin —</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['id'] ?>" <?= (string) $r['id'] === $form['role_id'] ? 'selected' : '' ?>>
                                <?= e((string) $r['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['role_id'])): ?><small class="form-error"><?= e($errors['role_id']) ?></small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Şifre</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                               autocomplete="new-password" minlength="8" required>
                        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Şifreyi göster/gizle">
                            <svg class="password-toggle__eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?><small class="form-error"><?= e($errors['password']) ?></small><?php else: ?><small class="form-hint">En az 8 karakter.</small><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="status">Durum</label>
                    <select id="status" name="status" class="form-control">
                        <option value="1" <?= $form['status'] === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= $form['status'] === '0' ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Kullanıcıyı Kaydet</button>
                <a class="btn btn--ghost" href="<?= e(url('modules/settings/users.php')) ?>">Vazgeç</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
