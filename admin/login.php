<?php
require_once dirname(__DIR__) . '/app/core/bootstrap.php';

// Zaten girişliyse panele gönder
if (Auth::check()) {
    redirect(base_url('admin/dashboard.php'));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::check();
    if (Auth::isLocked()) {
        $mins = (int) ceil(Auth::lockRemaining() / 60);
        $error = "Çok fazla hatalı deneme yapıldı. Lütfen {$mins} dakika sonra tekrar deneyin.";
    } else {
        $email = post('email');
        $pass  = (string) ($_POST['password'] ?? '');
        if ($email === '' || $pass === '') {
            $error = 'E-posta ve şifre alanları zorunludur.';
        } elseif (Auth::attempt($email, $pass)) {
            redirect(base_url('admin/dashboard.php'));
        } else {
            $error = Auth::isLocked()
                ? 'Çok fazla hatalı deneme yapıldı. Lütfen 15 dakika sonra tekrar deneyin.'
                : 'E-posta veya şifre hatalı.';
        }
    }
}
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Yönetim Girişi — <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">D4<span>s</span>tattoo</div>
    <h1>Yönetim Paneli</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" action="" autocomplete="off" novalidate>
        <?= Csrf::field() ?>
        <label for="email">E-posta</label>
        <input type="email" id="email" name="email" required autofocus
               value="<?= e(post('email')) ?>" placeholder="ornek@eposta.com">
        <label for="password">Şifre</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
        <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
    </form>
    <a class="login-back" href="<?= e(base_url()) ?>">← Siteye dön</a>
</div>
</body>
</html>
