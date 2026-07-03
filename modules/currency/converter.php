<?php

declare(strict_types=1);

/**
 * Kur çevirici bileşeni.
 *
 * İki şekilde kullanılabilir:
 *   1) Başka bir sayfa içine include edilir (yalnızca form parçası basılır).
 *   2) Doğrudan açılır (kendi tam sayfa düzeniyle render edilir).
 *
 * Hesaplama için güncel kur verisi kur.php JSON servisinden alınır; JS ile
 * anlık hesaplama yapılır.
 */

/**
 * Çevirici form parçasını basar.
 */
function render_currency_converter(): void
{
    ?>
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
    <?php
}

/* ---------------------------------------------------------------------------
 * Doğrudan erişim: tam sayfa render
 * ------------------------------------------------------------------------- */
$scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? realpath((string) $_SERVER['SCRIPT_FILENAME']) : '';
$thisFile   = realpath(__FILE__);

if ($scriptFile !== false && $thisFile !== false && $scriptFile === $thisFile) {
    require_once __DIR__ . '/../../includes/auth.php';
    require_once __DIR__ . '/../../includes/permissions.php';
    require_once __DIR__ . '/../../kur.php';

    require_permission('currency.view');

    $rateData = currency_get_rates();

    $page_title = 'Kur Çevirici';
    $active_nav = 'currency';
    require __DIR__ . '/../../includes/layout-header.php';
    ?>

    <section class="page-head">
        <div>
            <h2 class="page-head__title">Kur Çevirici</h2>
            <p class="page-head__subtitle">TRY · USD · EUR arası hızlı dönüşüm.</p>
        </div>
        <div class="page-head__actions">
            <a class="btn btn--ghost" href="<?= e(url('modules/currency/index.php')) ?>">&larr; Kur modülü</a>
        </div>
    </section>

    <div class="card">
        <div class="card__body">
            <?php render_currency_converter(); ?>
        </div>
    </div>

    <?php
    require __DIR__ . '/../../includes/layout-footer.php';
    return;
}

// Include olarak çağrıldıysa yalnızca form parçasını bas.
render_currency_converter();
