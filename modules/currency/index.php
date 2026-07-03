<?php

declare(strict_types=1);

/**
 * Kur modülü — genel bakış.
 *
 * Güncel kurları gösterir ve kur çeviriciye erişim sağlar. Kur verisi
 * kur.php üzerinden alınır.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/../../kur.php';

require_permission('currency.view');

$rateData  = currency_get_rates();
$usdTry    = $rateData['rates']['USD'] ?? 0.0;
$eurTry    = $rateData['rates']['EUR'] ?? 0.0;
$rateStale = (bool) ($rateData['stale'] ?? false);

$page_title = 'Kur Çevirici';
$active_nav = 'currency';
require __DIR__ . '/../../includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Kur Çevirici</h2>
        <p class="page-head__subtitle">Güncel kurlar ve TRY · USD · EUR dönüşümü.</p>
    </div>
</section>

<div class="stat-grid stat-grid--2">
    <div class="card stat-card">
        <div class="stat-card__label">Dolar (USD/TRY)</div>
        <div class="stat-card__value"><?= $usdTry > 0 ? e(number_format($usdTry, 4, ',', '.')) . ' ₺' : '—' ?></div>
        <div class="stat-card__meta">1 USD karşılığı</div>
    </div>
    <div class="card stat-card">
        <div class="stat-card__label">Euro (EUR/TRY)</div>
        <div class="stat-card__value"><?= $eurTry > 0 ? e(number_format($eurTry, 4, ',', '.')) . ' ₺' : '—' ?></div>
        <div class="stat-card__meta">1 EUR karşılığı</div>
    </div>
</div>

<?php if ($rateStale): ?>
    <div class="alert alert--warning" role="status">
        Kur servisi şu anda güncellenemiyor; en son kaydedilen veriler gösteriliyor.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card__header">
        <h3 class="card__title">Kur Çevirici</h3>
        <span class="card__hint">Kaynak: <?= e($rateData['source'] === 'api' ? 'Canlı (API)' : ($rateData['source'] === 'cache' ? 'Önbellek' : 'Yok')) ?></span>
    </div>
    <div class="card__body">
        <?php require __DIR__ . '/converter.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/../../includes/layout-footer.php'; ?>
