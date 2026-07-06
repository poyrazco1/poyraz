<?php
/** Sık Sorulan Sorular — FAQ schema ile */
$faqs = rows_lang('SELECT * FROM faq_items WHERE lang = ? AND status = 1 ORDER BY sort_order');
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.faq'), 'path' => 'sss'],
];
$meta = [
    'title'       => t('title.faq') . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('title.faq') . ': ' . setting('site_description'),
    'canonical'   => 'sss',
];
$extraHead = jsonld_breadcrumb($breadcrumbs) . jsonld_faq($faqs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.faq')) ?></h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="faq-list">
            <?php foreach ($faqs as $f): ?>
            <div class="faq-item">
                <button type="button" class="faq-q"><?= e($f['question']) ?></button>
                <div class="faq-a"><div class="faq-a-inner"><?= e($f['answer']) ?></div></div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="cta-band" style="margin-top:48px">
            <div>
                <h2><?= e(t('home.contact_title')) ?></h2>
                <p><?= e(t('home.contact_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp')) ?></a>
                <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
