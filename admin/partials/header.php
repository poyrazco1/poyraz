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
        'dashboard'  => ['dashboard.php', 'Dashboard', 0, 'dashboard'],
        'settings'   => ['settings.php', 'Site Ayarları', 0, 'settings'],
        'languages'  => ['languages.php', 'Dil Yönetimi', 0, 'language'],
    ],
    'İçerik' => [
        'pages'      => ['pages.php', 'Sayfalar', 0, 'edit'],
        'services'   => ['services.php', 'Hizmetler', 0, 'brush'],
        'blog'       => ['blog.php', 'Blog Yazıları', 0, 'blog'],
        'blog-categories' => ['blog-categories.php', 'Blog Kategorileri', 0, 'filter'],
        'gallery'    => ['gallery.php', 'Galeri', 0, 'gallery'],
        'gallery-categories' => ['gallery-categories.php', 'Galeri Kategorileri', 0, 'image'],
        'faq'        => ['faq.php', 'SSS', 0, 'faq'],
        'location-pages' => ['location-pages.php', 'Lokasyon SEO', 0, 'location'],
        'prices'     => ['prices.php', 'Fiyat Listesi', 0, 'price'],
        'testimonials' => ['testimonials.php', 'Yorumlar', 0, 'quote'],
        'campaigns'  => ['campaigns.php', 'Kampanyalar', 0, 'campaign'],
    ],
    'Talepler' => [
        'appointments' => ['appointments.php', 'Randevular', $newAppointments, 'calendar'],
        'quotes'       => ['quotes.php', 'Fiyat Teklifleri', $newQuotes, 'price'],
        'messages'     => ['messages.php', 'İletişim Mesajları', $newMessages, 'mail'],
        'tracking'     => ['tracking.php', 'Müşteri Takip', 0, 'search'],
    ],
];
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle) ?> — <?= e(setting('site_name', SITE_NAME)) ?> Panel</title>
<link rel="icon" href="<?= e(upload_url(setting('favicon'), 'img/favicon.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body>
<div class="admin-wrap">
    <aside class="sidebar" id="sidebar">
        <a class="sidebar-brand" href="<?= e(admin_url('dashboard.php')) ?>">
            <?php if (setting('logo')): ?>
            <img src="<?= e(upload_url(setting('logo'))) ?>" alt="<?= e(setting('site_name', SITE_NAME)) ?>" class="sidebar-logo">
            <?php else: ?>
            D4<span>s</span>tattoo
            <?php endif; ?>
        </a>
        <ul class="side-nav">
            <?php foreach ($menu as $section => $items): ?>
            <li class="nav-sec"><?= e($section) ?></li>
            <?php foreach ($items as $key => [$href, $label, $count, $icn]): ?>
            <li>
                <a href="<?= e(admin_url($href)) ?>" class="<?= $active === $key ? 'active' : '' ?>">
                    <span class="menu-label"><?= icon($icn, 'admin-menu-icon icon-sm') ?> <?= e($label) ?></span>
                    <?php if ($count > 0): ?><span class="count"><?= $count ?></span><?php endif; ?>
                </a>
            </li>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <li class="nav-sec">Hesap</li>
            <li><a href="<?= e(base_url()) ?>" target="_blank"><span class="menu-label"><?= icon('home', 'admin-menu-icon icon-sm') ?> Siteyi Görüntüle ↗</span></a></li>
            <li><a href="<?= e(admin_url('logout.php', ['t' => Csrf::token()])) ?>"><span class="menu-label"><?= icon('logout', 'admin-menu-icon icon-sm') ?> Çıkış Yap</span></a></li>
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
