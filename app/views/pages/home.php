<?php
/** Ana sayfa */
$services = rows_lang('SELECT * FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 6');
$galleryCats = rows_lang('SELECT * FROM gallery_categories WHERE lang = ? AND status = 1 ORDER BY sort_order');
$galleryItems = Database::all(
    "SELECT gi.*, gc.slug AS cat_slug, gc.name AS cat_name
     FROM gallery_items gi LEFT JOIN gallery_categories gc ON gc.id = gi.category_id
     WHERE gi.status = 1 AND gi.type = 'image' ORDER BY gi.sort_order LIMIT 8"
);
$prices = rows_lang('SELECT * FROM price_list_items WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 4');
$posts = rows_lang('SELECT * FROM blog_posts WHERE lang = ? AND status = 1 ORDER BY published_at DESC LIMIT 3');
$testimonials = rows_lang('SELECT * FROM testimonials WHERE lang = ? AND status = 1 ORDER BY id DESC LIMIT 3');
$campaign = setting('campaign_show', '1') === '1'
    ? row_lang("SELECT * FROM campaigns WHERE lang = ? AND status = 1
                AND (start_date IS NULL OR start_date <= CURDATE())
                AND (end_date IS NULL OR end_date >= CURDATE())
                ORDER BY id DESC LIMIT 1")
    : null;

$meta = [
    'title'       => setting('site_slogan') . ' | ' . setting('location_text', 'İstanbul / Bağcılar'),
    'description' => setting('site_description'),
    'canonical'   => '',
];
require BASE_PATH . '/app/views/layout/header.php';
?>

<?php if ($campaign): ?>
<div class="campaign-band">
    <div class="container">
        <span class="camp-label"><?= e(t('home.campaign_label')) ?></span>
        <span><strong><?= e($campaign['title']) ?></strong> — <?= e($campaign['description']) ?></span>
        <?php if ($campaign['button_text']): ?>
        <a href="<?= e($campaign['button_url'] ?: url('randevu-al')) ?>"><?= e($campaign['button_text']) ?></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<section class="hero">
    <div class="container hero-inner">
        <span class="hero-kicker"><?= e(setting('location_text', 'İstanbul / Bağcılar')) ?> — Tattoo Studio</span>
        <h1><?= e(setting('hero_title', t('home.services_title'))) ?></h1>
        <p class="hero-sub"><?= e(setting('hero_subtitle')) ?></p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(setting('hero_btn_primary', t('btn.appointment'))) ?></a>
            <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(setting('hero_btn_secondary', t('btn.whatsapp_quote'))) ?></a>
        </div>
    </div>
</section>

<div class="badges">
    <div class="container">
        <ul class="badges-list">
            <li><?= icon('needle', 'icon-sm') ?> <?= e(t('badge.single_needle')) ?></li>
            <li><?= icon('shield', 'icon-sm') ?> <?= e(t('badge.sterile')) ?></li>
            <li><?= icon('sparkle', 'icon-sm') ?> <?= e(t('badge.custom')) ?></li>
            <li><?= icon('star', 'icon-sm') ?> <?= e(t('badge.experience', ['years' => setting('experience_years', '3')])) ?></li>
            <li><?= icon('clock', 'icon-sm') ?> <?= e(setting('working_hours', t('badge.hours'))) ?></li>
        </ul>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="section-head center">
            <h2><?= e(t('home.services_title')) ?></h2>
            <p><?= e(t('home.services_sub')) ?></p>
        </div>
        <div class="grid grid-3">
            <?php foreach ($services as $s): ?>
            <article class="card">
                <a class="card-img" href="<?= e(url('hizmetler/' . $s['slug'])) ?>">
                    <img src="<?= e(media_url($s['image'], service_demo_image($s['slug']))) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
                </a>
                <div class="card-body">
                    <div class="card-title-row">
                        <?= icon(service_icon_name($s['icon']), 'icon-md') ?>
                        <h3><a href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></h3>
                    </div>
                    <p><?= e($s['short_description']) ?></p>
                    <a class="card-link" href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e(t('btn.details')) ?> <?= icon('arrow-right', 'icon-sm') ?></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('hizmetler')) ?>"><?= e(t('nav.all_services')) ?></a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container artist-wrap">
        <div class="artist-photo">
            <img src="<?= e(upload_url(setting('artist_image'), 'img/demo/artist.svg')) ?>" alt="<?= e(setting('artist_name', 'D4stattoo Artist')) ?>" loading="lazy">
        </div>
        <div class="artist-info">
            <h2><?= e(t('home.artist_title')) ?> — <span><?= e(setting('artist_name', 'D4stattoo Artist')) ?></span></h2>
            <p><?= e(setting('artist_bio')) ?></p>
            <ul class="artist-tags">
                <li><?= e(t('badge.custom')) ?></li>
                <li><?= e(t('badge.consult')) ?></li>
                <li><?= e(t('badge.experience', ['years' => setting('experience_years', '3')])) ?></li>
            </ul>
            <a class="btn btn-outline" href="<?= e(url('sanatci')) ?>"><?= e(t('btn.details')) ?></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2><?= e(t('home.gallery_title')) ?></h2>
            <p><?= e(t('home.gallery_sub')) ?></p>
        </div>
        <div class="gallery-filters">
            <button type="button" class="filter-btn active" data-filter="all"><?= e(t('gallery.all')) ?></button>
            <?php foreach ($galleryCats as $c): ?>
            <button type="button" class="filter-btn" data-filter="<?= e($c['slug']) ?>"><?= e($c['name']) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="gallery-grid">
            <?php if (empty($galleryItems)) { $galleryItems = demo_gallery_items(); } ?>
            <?php foreach ($galleryItems as $g): $gImg = media_url($g['image'], gallery_fallback_image()); ?>
            <button type="button" class="gallery-item" data-cat="<?= e($g['cat_slug'] ?? '') ?>"
                    data-lightbox="<?= e($gImg) ?>" data-caption="<?= e($g['title']) ?>">
                <img src="<?= e($gImg) ?>" alt="<?= e($g['alt_text'] ?: $g['title']) ?>" loading="lazy">
                <span class="gi-label"><?= e($g['title']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('galeri')) ?>"><?= e(t('btn.view_gallery')) ?></a>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head center">
            <h2><?= e(t('home.hygiene_title')) ?></h2>
        </div>
        <div class="hygiene-grid">
            <div class="hyg-item"><div class="hyg-icon"><?= icon('needle') ?></div><h3><?= e(t('badge.single_needle')) ?></h3><p><?= e(t('badge.sterile')) ?> — <?= e(t('badge.custom')) ?></p></div>
            <div class="hyg-item"><div class="hyg-icon"><?= icon('shield') ?></div><h3><?= e(t('badge.sterile')) ?></h3><p><?= e(t('title.hygiene')) ?></p></div>
            <div class="hyg-item"><div class="hyg-icon"><?= icon('user') ?></div><h3><?= e(t('badge.consult')) ?></h3><p><?= e(t('service.cta_text')) ?></p></div>
            <div class="hyg-item"><div class="hyg-icon"><?= icon('sparkle') ?></div><h3><?= e(t('nav.aftercare')) ?></h3><p><?= e(t('home.blog_sub')) ?></p></div>
        </div>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('hijyen')) ?>"><?= e(t('nav.hygiene')) ?> →</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2><?= e(t('home.quote_cta_title')) ?></h2>
                <p><?= e(t('home.quote_cta_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-primary" href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a>
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp')) ?></a>
            </div>
        </div>
    </div>
</section>

<?php if ($prices): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head center">
            <h2><?= e(t('home.prices_title')) ?></h2>
        </div>
        <div class="price-list">
            <?php foreach ($prices as $p): ?>
            <div class="price-item">
                <div><h3><?= e($p['title']) ?></h3><p class="price-desc"><?= e($p['description']) ?></p></div>
                <span class="price-tag"><?= e($p['price_text']) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="price-note"><?= e(t('home.prices_note')) ?></p>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('fiyat-listesi')) ?>"><?= e(t('nav.prices')) ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2><?= e(t('home.blog_title')) ?></h2>
            <p><?= e(t('home.blog_sub')) ?></p>
        </div>
        <div class="grid grid-3">
            <?php foreach ($posts as $p): ?>
            <article class="card">
                <a class="card-img" href="<?= e(url('blog/' . $p['slug'])) ?>">
                    <img src="<?= e(media_url($p['cover_image'], blog_fallback_image())) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                </a>
                <div class="card-body">
                    <span class="card-meta"><?= e(format_date($p['published_at'])) ?></span>
                    <h3><a href="<?= e(url('blog/' . $p['slug'])) ?>"><?= e($p['title']) ?></a></h3>
                    <p><?= e(excerpt_of($p['excerpt'] ?: $p['content'], 110)) ?></p>
                    <a class="card-link" href="<?= e(url('blog/' . $p['slug'])) ?>"><?= e(t('btn.read_more')) ?> →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="section-more">
            <a class="btn btn-outline" href="<?= e(url('blog')) ?>"><?= e(t('btn.all_posts')) ?></a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head center">
            <h2><?= e(t('home.testimonials_title')) ?></h2>
        </div>
        <div class="testi-grid">
            <?php foreach ($testimonials as $tst): ?>
            <div class="testi-card">
                <span class="testi-stars"><?= str_repeat('★', (int) $tst['rating']) . str_repeat('☆', max(0, 5 - (int) $tst['rating'])) ?></span>
                <blockquote>“<?= e($tst['comment']) ?>”</blockquote>
                <div class="testi-meta">
                    <strong><?= e($tst['name']) ?></strong>
                    <span class="testi-source"><?= e($tst['source']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2><?= e(t('home.contact_title')) ?></h2>
                <p><?= e(t('home.contact_text')) ?></p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp')) ?></a>
                <a class="btn btn-outline" href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener"><?= e(t('btn.instagram')) ?></a>
                <a class="btn btn-primary" href="<?= e(url('iletisim')) ?>"><?= e(t('nav.contact')) ?></a>
            </div>
        </div>
    </div>
</section>

<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="<?= e(t('btn.close')) ?>">×</button>
    <img src="" alt="">
    <div class="lightbox-caption"></div>
</div>

<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
