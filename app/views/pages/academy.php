<?php
/** Akademi — eğitici içerikler (Akademi kategorisindeki blog yazıları) */
$cat = row_lang("SELECT * FROM blog_categories WHERE lang = ? AND slug = 'akademi' AND status = 1");
$posts = $cat
    ? rows_lang('SELECT * FROM blog_posts WHERE lang = ? AND status = 1 AND category_id = ? ORDER BY published_at DESC', [(int) $cat['id']])
    : [];

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.academy'), 'path' => 'akademi'],
];
$meta = [
    'title'       => t('title.academy') . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('home.blog_sub'),
    'canonical'   => 'akademi',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.academy')) ?></h1>
        <p class="lead"><?= e(t('home.blog_sub')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($posts): ?>
        <div class="grid grid-3">
            <?php foreach ($posts as $p): ?>
            <article class="card">
                <a class="card-img" href="<?= e(url('blog/' . $p['slug'])) ?>">
                    <img src="<?= e(upload_url($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                </a>
                <div class="card-body">
                    <span class="card-meta"><?= e(format_date($p['published_at'])) ?></span>
                    <h3><a href="<?= e(url('blog/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
                    <p><?= e(excerpt_of($p['excerpt'] ?: $p['content'], 120)) ?></p>
                    <a class="card-link" href="<?= e(url('blog/' . $p['slug'])) ?>"><?= e(t('btn.read_more')) ?> →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="gallery-empty"><?= e(t('blog.empty')) ?></p>
        <?php endif; ?>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('blog')) ?>"><?= e(t('btn.all_posts')) ?></a>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
