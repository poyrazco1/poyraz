<?php
/**
 * Genel içerik sayfası — pages tablosundan slug ile yüklenir.
 * about.php / hygiene.php / aftercare.php / kvkk.php bu dosyayı sarmalar.
 */
$slug = $slug ?? null;
if (!$slug) {
    http_response_code(404);
    require BASE_PATH . '/app/views/pages/404.php';
    return;
}

$page = row_lang('SELECT * FROM pages WHERE lang = ? AND slug = ? AND status = 1 LIMIT 1', [$slug]);
if (!$page) {
    http_response_code(404);
    require BASE_PATH . '/app/views/pages/404.php';
    return;
}

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => $page['title'], 'path' => $slug],
];
$meta = [
    'title'       => $page['meta_title'] ?: $page['title'],
    'description' => $page['meta_description'] ?: excerpt_of($page['body'] ?? ''),
    'canonical'   => $slug,
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e($page['title']) ?></h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="prose"><?= clean_html($page['body'] ?? '') ?></div>
        <div class="cta-band" style="margin-top:48px">
            <div>
                <h2><?= e(t('service.cta_title')) ?></h2>
                <p><?= e(t('service.cta_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
