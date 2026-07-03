<?php

declare(strict_types=1);

/**
 * Ayarlar modülü — kullanıcı düzenleme.
 *
 * Şifre alanı boş bırakılırsa mevcut şifre korunur. Sistemde en az bir aktif
 * yönetici kalması güvence altına alınır.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../includes/csrf.php';

require_permission('users.manage');

$currentUser = current_user();

$userId = (int) input('id');
if ($userId <= 0) {
    flash('error', 'Geçersiz kullanıcı.');
    redirect('/modules/settings/users.php');
}

$stmt = db()->prepare(
    'SELECT u.*, r.slug AS role_slug FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1'
);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if ($user === false) {
    flash('error', 'Kullanıcı bulunamadı.');
    redirect('/modules/settings/users.php');
}

$roles = db()->query('SELECT id, name FROM roles ORDER BY name ASC')->fetchAll();

/**
 * Sistemdeki aktif yönetici sayısını döndürür.
 */
function active_admin_count(): int
{
    return (int) db()->query(
        "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id
         WHERE r.slug = 'yonetici' AND u.status = 1"
    )->fetchColumn();
}

$errors = [];
$form = [
    'name'     => (string) $user['name'],
    'email'    => (string) $user['email'],
    'username' => (string) $user['username'],
    'role_id'  => (string) $user['role_id'],
    'status'   => (string) $user['status'],
];

if (is_post()) {
    csrf_require('/modules/settings/user-edit.php?id=' . $userId);

    $form['name']     = input('name');
    $form['email']    = input('email');
    $form['username'] = input('username');
    $form['role_id']  = input('role_id');
    $form['status']   = input('status') === '0' ? '0' : '1';
    $password         = (string) ($_POST['password'] ?? '');

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
    if ($password !== '' && strlen($password) < 8) {
        $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
    }

    // Rol geçerli mi?
    $newRoleSlug = $user['role_slug'] ?? '';
    if (!isset($errors['role_id'])) {
        $chk = db()->prepare('SELECT slug FROM roles WHERE id = :id LIMIT 1');
        $chk->execute([':id' => (int) $form['role_id']]);
        $roleRow = $chk->fetch();
        if ($roleRow === false) {
            $errors['role_id'] = 'Seçilen rol geçersiz.';
        } else {
            $newRoleSlug = (string) $roleRow['slug'];
        }
    }

    // Benzersizlik (kendisi hariç).
    if (!isset($errors['email'])) {
        $chk = db()->prepare('SELECT COUNT(*) FROM users WHERE email = :e AND id <> :id');
        $chk->execute([':e' => $form['email'], ':id' => $userId]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['email'] = 'Bu e-posta başka bir kullanıcıda kayıtlı.';
        }
    }
    if (!isset($errors['username'])) {
        $chk = db()->prepare('SELECT COUNT(*) FROM users WHERE username = :u AND id <> :id');
        $chk->execute([':u' => $form['username'], ':id' => $userId]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors['username'] = 'Bu kullanıcı adı başka bir kullanıcıda kayıtlı.';
        }
    }

    // Son aktif yöneticiyi koru: yönetici rolünden çıkarma veya pasife alma.
    if (empty($errors) && ($user['role_slug'] ?? '') === 'yonetici' && (int) $user['status'] === 1) {
        $losesAdmin = ($newRoleSlug !== 'yonetici') || ($form['status'] === '0');
        if ($losesAdmin && active_admin_count() <= 1) {
            $errors['role_id'] = 'Sistemde en az bir aktif yönetici bulunmalıdır.';
        }
    }

    if (empty($errors)) {
        if ($password !== '') {
            $stmt = db()->prepare(
                'UPDATE users SET name = :name, email = :email, username = :username,
                        role_id = :role_id, status = :status, password_hash = :hash, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name'     => $form['name'],
                ':email'    => $form['email'],
                ':username' => $form['username'],
                ':role_id'  => (int) $form['role_id'],
                ':status'   => (int) $form['status'],
                ':hash'     => password_hash($password, PASSWORD_DEFAULT),
                ':id'       => $userId,
            ]);
        } else {
            $stmt = db()->prepare(
                'UPDATE users SET name = :name, email = :email, username = :username,
                        role_id = :role_id, status = :status, updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name'     => $form['name'],
                ':email'    => $form['email'],
                ':username' => $form['username'],
                ':role_id'  => (int) $form['role_id'],
                ':status'   => (int) $form['status'],
                ':id'       => $userId,
            ]);
        }

        flash('success', 'Kullanıcı güncellendi.');
        redirect('/modules/settings/users.php');
    }
}

$page_title = 'Kullanıcı Düzenle';
$active_nav = 'settings';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Kullanıcı Düzenle</h2>
        <p class="page-head__subtitle"><?= e((string) $user['username']) ?> hesabını düzenleyin.</p>
    </div>
    <div class="page-head__actions">
        <a class="btn btn--ghost" href="<?= e(url('modules/settings/users.php')) ?>">&larr; Listeye dön</a>
    </div>
</section>

<div class="card card--form">
    <div class="card__body">
        <form method="post" action="<?= e(url('modules/settings/user-edit.php?id=' . $userId)) ?>" novalidate>
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
                    <label for="password">Yeni Şifre</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                               autocomplete="new-password" minlength="8">
                        <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Şifreyi göster/gizle">
                            <svg class="password-toggle__eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7zm0 11a4 4 0 110-8 4 4 0 010 8zm0-2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?><small class="form-error"><?= e($errors['password']) ?></small><?php else: ?><small class="form-hint">Boş bırakırsanız mevcut şifre korunur.</small><?php endif; ?>
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
                <button type="submit" class="btn btn--primary">Değişiklikleri Kaydet</button>
                <a class="btn btn--ghost" href="<?= e(url('modules/settings/users.php')) ?>">Vazgeç</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
