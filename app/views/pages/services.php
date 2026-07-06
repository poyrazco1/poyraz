<?php
/** Hizmetler listesi */
$services = rows_lang('SELECT * FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order');
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.services'), 'path' => 'hizmetler'],
];
$meta = [
    'title'       => t('title.services') . ' — Tattoo & Dövme Stilleri',
    'description' => t('home.services_sub') . ' ' . setting('site_description'),
    'canonical'   => 'hizmetler',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.services')) ?></h1>
        <p class="lead"><?= e(t('home.services_sub')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ($services as $s): ?>
            <article class="card">
                <a class="card-img" href="<?= e(url('hizmetler/' . $s['slug'])) ?>">
                    <img src="<?= e(media_url($s['image'], service_demo_image($s['slug']))) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                </a>
                <div class="card-body">
                    <h3><a href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></h3>
                    <p><?= e($s['short_description']) ?></p>
                    <a class="card-link" href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e(t('btn.details')) ?> →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
