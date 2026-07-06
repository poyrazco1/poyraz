<?php
/** Blog yazı detayı — Article schema ile */
$post = row_lang('SELECT * FROM blog_posts WHERE lang = ? AND slug = ? AND status = 1 LIMIT 1', [$slug]);
if (!$post) {
    http_response_code(404);
    require BASE_PATH . '/app/views/pages/404.php';
    return;
}
$category = $post['category_id']
    ? Database::row('SELECT * FROM blog_categories WHERE id = ?', [$post['category_id']])
    : null;
$related = rows_lang(
    'SELECT * FROM blog_posts WHERE lang = ? AND status = 1 AND id != ? ORDER BY published_at DESC LIMIT 3',
    [$post['id']]
);

$urlPath = 'blog/' . $post['slug'];
$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.blog'), 'path' => 'blog'],
    ['name' => $post['title'], 'path' => $urlPath],
];
$meta = [
    'title'       => $post['meta_title'] ?: $post['title'],
    'description' => $post['meta_description'] ?: ($post['excerpt'] ?: excerpt_of($post['content'] ?? '')),
    'canonical'   => $urlPath,
    'image'       => $post['cover_image'],
    'type'        => 'article',
    'published'   => $post['published_at'],
    'modified'    => $post['updated_at'],
];
$extraHead = jsonld_breadcrumb($breadcrumbs) . jsonld_article($post, $urlPath);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e($post['title']) ?></h1>
    </div>
</section>
<section class="section">
    <div class="container detail-layout">
        <article>
            <div class="post-meta">
                <span><?= e(t('blog.published')) ?>: <?= e(format_date($post['published_at'])) ?></span>
                <?php if ($category): ?>
                <span><a href="<?= e(url('blog')) ?>?kategori=<?= e($category['slug']) ?>" style="color:var(--red)"><?= e($category['name']) ?></a></span>
                <?php endif; ?>
            </div>
            <?php if ($post['cover_image']): ?>
            <div class="post-cover">
                <img src="<?= e(upload_url($post['cover_image'])) ?>" alt="<?= e($post['title']) ?>">
            </div>
            <?php endif; ?>
            <div class="prose"><?= clean_html($post['content'] ?? '') ?></div>
            <?php if ($post['tags']): ?>
            <div class="tag-list">
                <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                <span>#<?= e($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </article>
        <aside class="sidebar">
            <div class="side-card side-cta">
                <h3><?= e(t('service.cta_title')) ?></h3>
                <p><?= e(t('service.cta_text')) ?></p>
                <a class="btn btn-primary btn-block" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
                <a class="btn btn-wa btn-block" style="margin-top:10px" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
            </div>
            <?php if ($related): ?>
            <div class="side-card">
                <h3><?= e(t('blog.related')) ?></h3>
                <ul>
                    <?php foreach ($related as $r): ?>
                    <li><a href="<?= e(url('blog/' . $r['slug'])) ?>"><?= e($r['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </aside>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
