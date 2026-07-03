<?php

declare(strict_types=1);

/**
 * Panel ana sayfası (dashboard).
 *
 * Hoş geldiniz alanı, güncel USD/EUR kuru, kur çevirici, özet kartları ve
 * ayarlara hızlı erişim. Kur verisi doğrudan değil kur.php üzerinden alınır.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/permissions.php';
require_once __DIR__ . '/kur.php';

require_login();

$currentUser = current_user();

// Kur verisi (kur.php fonksiyonları).
$rateData  = currency_get_rates();
$usdTry    = $rateData['rates']['USD'] ?? 0.0;
$eurTry    = $rateData['rates']['EUR'] ?? 0.0;
$rateStale = (bool) ($rateData['stale'] ?? false);

// Özet kartları için veriler.
$userCount = 0;
$roleCount = 0;
try {
    $userCount = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $roleCount = (int) db()->query('SELECT COUNT(*) FROM roles')->fetchColumn();
} catch (Throwable $e) {
    app_log('Dashboard sayaç hatası: ' . $e->getMessage(), 'WARN');
}

$greetingName = $currentUser['name'] ?? ($currentUser['username'] ?? 'Kullanıcı');
$lastLogin    = format_datetime($currentUser['last_login_at'] ?? null);

$page_title = 'Panel';
$active_nav = 'dashboard';
require __DIR__ . '/includes/layout-header.php';
?>

<section class="page-head">
    <div>
        <h2 class="page-head__title">Hoş geldiniz, <?= e((string) $greetingName) ?></h2>
        <p class="page-head__subtitle">Sistemin güncel durumuna buradan göz atabilirsiniz.</p>
    </div>
</section>

<div class="stat-grid">
    <div class="card stat-card">
        <div class="stat-card__label">Güncel Dolar (USD/TRY)</div>
        <div class="stat-card__value"><?= $usdTry > 0 ? e(number_format($usdTry, 4, ',', '.')) . ' ₺' : '—' ?></div>
        <div class="stat-card__meta">1 USD karşılığı</div>
    </div>

    <div class="card stat-card">
        <div class="stat-card__label">Güncel Euro (EUR/TRY)</div>
        <div class="stat-card__value"><?= $eurTry > 0 ? e(number_format($eurTry, 4, ',', '.')) . ' ₺' : '—' ?></div>
        <div class="stat-card__meta">1 EUR karşılığı</div>
    </div>

    <div class="card stat-card">
        <div class="stat-card__label">Kullanıcı Sayısı</div>
        <div class="stat-card__value"><?= e((string) $userCount) ?></div>
        <div class="stat-card__meta">Kayıtlı kullanıcı</div>
    </div>

    <div class="card stat-card">
        <div class="stat-card__label">Rol Sayısı</div>
        <div class="stat-card__value"><?= e((string) $roleCount) ?></div>
        <div class="stat-card__meta">Tanımlı rol</div>
    </div>
</div>

<?php if ($rateStale): ?>
    <div class="alert alert--warning" role="status">
        Kur servisi şu anda güncellenemiyor; en son kaydedilen veriler gösteriliyor.
    </div>
<?php endif; ?>

<div class="grid grid--2">
    <div class="card">
        <div class="card__header">
            <h3 class="card__title">Kur Çevirici</h3>
            <span class="card__hint">TRY · USD · EUR</span>
        </div>
        <div class="card__body">
            <form class="converter" data-converter data-converter-endpoint="<?= e(url('kur.php')) ?>" novalidate>
                <div class="converter__row">
                    <div class="form-group">
                        <label for="conv-amount">Tutar</label>
                        <input type="text" id="conv-amount" class="form-control" inputmode="decimal"
                               value="1" data-converter-amount>
                    </div>
                    <div class="form-group">
                        <label for="conv-from">Kaynak</label>
                        <select id="conv-from" class="form-control" data-converter-from>
                            <option value="USD">USD — Amerikan Doları</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="TRY">TRY — Türk Lirası</option>
                        </select>
                    </div>
                    <div class="form-group converter__swap-wrap">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn--ghost converter__swap" data-converter-swap
                                aria-label="Para birimlerini değiştir">⇄</button>
                    </div>
                    <div class="form-group">
                        <label for="conv-to">Hedef</label>
                        <select id="conv-to" class="form-control" data-converter-to>
                            <option value="TRY">TRY — Türk Lirası</option>
                            <option value="USD">USD — Amerikan Doları</option>
                            <option value="EUR">EUR — Euro</option>
                        </select>
                    </div>
                </div>

                <div class="converter__result" data-converter-result aria-live="polite">
                    <span class="converter__result-label">Sonuç</span>
                    <span class="converter__result-value" data-converter-output>—</span>
                </div>
                <p class="converter__note" data-converter-note>Kurlar kur.php üzerinden alınır.</p>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <h3 class="card__title">Hızlı Erişim</h3>
        </div>
        <div class="card__body">
            <ul class="quick-links">
                <?php if (user_can('settings.view')): ?>
                    <li><a href="<?= e(url('modules/settings/index.php')) ?>">Ayarlar</a></li>
                    <li><a href="<?= e(url('modules/settings/users.php')) ?>">Kullanıcı Yönetimi</a></li>
                    <li><a href="<?= e(url('modules/settings/roles.php')) ?>">Rol Yönetimi</a></li>
                <?php endif; ?>
                <li><a href="<?= e(url('modules/currency/index.php')) ?>">Kur Çevirici</a></li>
            </ul>

            <div class="info-list">
                <div class="info-list__row">
                    <span class="info-list__key">Rolünüz</span>
                    <span class="info-list__val"><?= e((string) ($currentUser['role_name'] ?? '—')) ?></span>
                </div>
                <div class="info-list__row">
                    <span class="info-list__key">Son giriş</span>
                    <span class="info-list__val"><?= e($lastLogin) ?></span>
                </div>
                <div class="info-list__row">
                    <span class="info-list__key">Kur kaynağı</span>
                    <span class="info-list__val"><?= e($rateData['source'] === 'api' ? 'Canlı (API)' : ($rateData['source'] === 'cache' ? 'Önbellek' : 'Yok')) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
