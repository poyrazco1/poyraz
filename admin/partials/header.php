<?php
/**
 * Admin HTML başlangıcı + sidebar + topbar.
 * $pageTitle: sayfa başlığı, $active: aktif menü anahtarı
 */
$pageTitle = $pageTitle ?? 'Panel';
$active = $active ?? '';

$newAppointments = (int) Database::value("SELECT COUNT(*) FROM appointments WHERE status = 'new'");
$newQuotes = (int) Database::value("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'");
$newMessages = (int) Database::value("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");

$menu = [
    'Genel' => [
        'dashboard'  => ['dashboard.php', 'Dashboard', 0],
        'settings'   => ['settings.php', 'Site Ayarları', 0],
        'languages'  => ['languages.php', 'Dil Yönetimi', 0],
    ],
    'İçerik' => [
        'pages'      => ['pages.php', 'Sayfalar', 0],
        'services'   => ['services.php', 'Hizmetler', 0],
        'blog'       => ['blog.php', 'Blog Yazıları', 0],
        'blog-categories' => ['blog-categories.php', 'Blog Kategorileri', 0],
        'gallery'    => ['gallery.php', 'Galeri', 0],
        'gallery-categories' => ['gallery-categories.php', 'Galeri Kategorileri', 0],
        'faq'        => ['faq.php', 'SSS', 0],
        'prices'     => ['prices.php', 'Fiyat Listesi', 0],
        'testimonials' => ['testimonials.php', 'Yorumlar', 0],
        'campaigns'  => ['campaigns.php', 'Kampanyalar', 0],
    ],
    'Talepler' => [
        'appointments' => ['appointments.php', 'Randevular', $newAppointments],
        'quotes'       => ['quotes.php', 'Fiyat Teklifleri', $newQuotes],
        'messages'     => ['messages.php', 'İletişim Mesajları', $newMessages],
        'tracking'     => ['tracking.php', 'Müşteri Takip', 0],
    ],
];
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle) ?> — <?= e(setting('site_name', SITE_NAME)) ?> Panel</title>
<link rel="icon" href="<?= e(upload_url(setting('favicon'), 'img/favicon.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="admin-wrap">
    <aside class="sidebar" id="sidebar">
        <a class="sidebar-brand" href="<?= e(admin_url('dashboard.php')) ?>">D4<span>s</span>tattoo</a>
        <ul class="side-nav">
            <?php foreach ($menu as $section => $items): ?>
            <li class="nav-sec"><?= e($section) ?></li>
            <?php foreach ($items as $key => [$href, $label, $count]): ?>
            <li>
                <a href="<?= e(admin_url($href)) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                    <?= e($label) ?>
                    <?php if ($count > 0): ?><span class="count"><?= $count ?></span><?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <li class="nav-sec">Hesap</li>
            <li><a href="<?= e(base_url()) ?>" target="_blank">Siteyi Görüntüle ↗</a></li>
            <li><a href="<?= e(admin_url('logout.php', ['t' => Csrf::token()])) ?>">Çıkış Yap</a></li>
        </ul>
    </aside>
    <div class="admin-main">
        <div class="topbar">
            <button type="button" class="burger" data-sidebar-toggle>☰ Menü</button>
            <strong><?= e($pageTitle) ?></strong>
            <div class="topbar-right">
                <span class="who"><?= e(Auth::name()) ?> · <?= e(Auth::email()) ?></span>
            </div>
        </div>
        <div class="content">
        <?php foreach (flash_get() as $f): ?>
            <div class="alert alert-<?= $f['type'] === 'success' ? 'success' : 'danger' ?>"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
