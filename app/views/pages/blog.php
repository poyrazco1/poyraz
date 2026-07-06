<?php
/** Blog listesi — kategori filtresi ve sayfalama destekli */
$catSlug = get_param('kategori');
$page = max(1, int_param('sayfa', 1));
$perPage = 9;

$where = 'lang = ? AND status = 1';
$params = [];
$cat = null;
if ($catSlug !== '') {
    $cat = row_lang('SELECT * FROM blog_categories WHERE lang = ? AND slug = ? AND status = 1', [$catSlug]);
    if ($cat) {
        $where .= ' AND category_id = ' . (int) $cat['id'];
    }
}

$posts = rows_lang(
    "SELECT * FROM blog_posts WHERE $where ORDER BY published_at DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage),
    $params
);
$cats = rows_lang('SELECT * FROM blog_categories WHERE lang = ? AND status = 1 ORDER BY name');

$pageTitle = $cat ? $cat['name'] : t('title.blog');
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => $pageTitle, 'path' => 'blog'],
];
$meta = [
    'title'       => $pageTitle . ' — ' . setting('site_name', SITE_NAME),
    'description' => t('home.blog_sub'),
    'canonical'   => 'blog',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e($pageTitle) ?></h1>
        <p class="lead"><?= e(t('home.blog_sub')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="gallery-filters">
            <a class="filter-btn <?= $catSlug === '' ? 'active' : '' ?>" href="<?= e(url('blog')) ?>"><?= e(t('gallery.all')) ?></a>
            <?php foreach ($cats as $c): ?>
            <a class="filter-btn <?= $catSlug === $c['slug'] ? 'active' : '' ?>" href="<?= e(url('blog')) ?>?kategori=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
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
        <?php if (count($posts) === $perPage): ?>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('blog')) ?>?<?= $catSlug ? 'kategori=' . e($catSlug) . '&' : '' ?>sayfa=<?= $page + 1 ?>"><?= e(t('btn.all_posts')) ?> →</a>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p class="gallery-empty"><?= e(t('blog.empty')) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
