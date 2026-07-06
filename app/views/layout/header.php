<?php
/**
 * Ortak üst şablon. Sayfa görünümleri $meta dizisini tanımlayıp bunu dahil eder.
 * $meta: title, description, canonical, image, type, published, modified, noindex
 * $extraHead: sayfaya özel ek <head> içeriği (schema JSON-LD vb.)
 */
$meta = $meta ?? [];
$isRtl = Lang::isRtl();
$logo = setting('logo');
$favicon = setting('favicon');
?><!doctype html>
<html lang="<?= e(Lang::current()) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?= seo_tags($meta) ?>
<link rel="icon" href="<?= e(upload_url($favicon, 'img/favicon.svg')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<?php $primary = setting('primary_color', ''); if ($primary && preg_match('/^#[0-9a-fA-F]{3,8}$/', $primary) && strtolower($primary) !== '#e11d2e'): ?>
<style>:root { --red: <?= e($primary) ?>; --red-dark: <?= e($primary) ?>; }</style>
<?php endif; ?>
<?= jsonld_local_business() ?>
<?= $extraHead ?? '' ?>
</head>
<body class="<?= $isRtl ? 'rtl' : '' ?>">
<a class="skip-link" href="#main"><?= e(t('nav.home')) ?></a>
<?php require BASE_PATH . '/app/views/layout/navbar.php'; ?>
<?php $flashes = flash_get(); if ($flashes): ?>
<div class="flash-wrap container">
    <?php foreach ($flashes as $f): ?>
    <div class="flash flash-<?= e($f['type']) ?>" role="alert"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<main id="main">
