<?php
/** Fiyat teklifi formu sayfası (POST işleme QuoteController'da) */
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.quote'), 'path' => 'fiyat-teklifi-al'],
];
$meta = [
    'title'       => t('title.quote') . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('form.quote_text'),
    'canonical'   => 'fiyat-teklifi-al',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('form.quote_title')) ?></h1>
        <p class="lead"><?= e(t('form.quote_text')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container form-wrap">
        <?php if ($errors): ?>
        <div class="flash flash-error"><?= e(t('form.error_general')) ?></div>
        <?php endif; ?>
        <div class="form-card">
            <form method="post" action="<?= e(url('fiyat-teklifi-al')) ?>" enctype="multipart/form-data" novalidate>
                <?= Csrf::field() ?>
                <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                <div class="form-grid">
                    <?php $withBudget = true; require BASE_PATH . '/app/views/layout/form-tattoo-fields.php'; ?>
                </div>
                <div class="form-actions">
                    <label class="form-consent">
                        <input type="checkbox" name="kvkk" value="1" required>
                        <span><?= str_replace(':url', e(url('kvkk')), t('form.kvkk_consent')) ?></span>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= e(t('btn.quote')) ?></button>
                    <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php old_clear(); require BASE_PATH . '/app/views/layout/footer.php'; ?>
