<?php

declare(strict_types=1);

/**
 * Panel düzeni — üst kısım (HTML head + kabuk açılışı).
 *
 * Kullanım (panel sayfalarında):
 *   $page_title = 'Sayfa Başlığı';
 *   $active_nav = 'dashboard'; // sidebar aktif öğe anahtarı
 *   require __DIR__ . '/includes/layout-header.php';
 *
 * Not: Bu dosya, çağıran sayfaya göre farklı derinliklerde include edilebilir.
 * Varlık (asset) yolları BASE_URL / url() yardımcısıyla güvenli üretilir.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';

$currentUser = current_user();
$pageTitle   = isset($page_title) && is_string($page_title) ? $page_title : 'Panel';
$activeNav   = isset($active_nav) && is_string($active_nav) ? $active_nav : '';
$flashes     = take_flashes();
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> · <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/css/app.css')) ?>">
</head>
<body>
<div class="app" data-app-shell>
    <?php require __DIR__ . '/layout-sidebar.php'; ?>

    <div class="app-backdrop" data-sidebar-backdrop hidden></div>

    <div class="app-main">
        <?php require __DIR__ . '/layout-topbar.php'; ?>

        <main class="content" role="main">
            <div class="container">
                <?php if (!empty($flashes)): ?>
                    <div class="flash-stack">
                        <?php foreach ($flashes as $flash): ?>
                            <?php $type = in_array($flash['type'], ['success', 'error', 'warning', 'info'], true) ? $flash['type'] : 'info'; ?>
                            <div class="alert alert--<?= e($type) ?>" role="alert">
                                <?= e($flash['message']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
