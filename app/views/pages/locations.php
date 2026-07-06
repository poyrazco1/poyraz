<?php
/** İstanbul lokasyon landing sayfası — /istanbul */
$all = rows_lang(
    "SELECT district, slug, intro, sort_order FROM location_pages WHERE lang = ? AND status = 1 ORDER BY sort_order, district"
);
// Öncelikli (sort_order < 100) ve diğerleri ayrı gruplanır
$featured = array_filter($all, fn ($r) => (int) $r['sort_order'] < 100);
$rest = array_filter($all, fn ($r) => (int) $r['sort_order'] >= 100);

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => 'İstanbul', 'path' => 'istanbul'],
];
$meta = [
    'title'       => 'İstanbul Tattoo Bölgeleri | İlçe ve Semt Dövme Rehberi | ' . SITE_NAME,
    'description' => 'İstanbul\'un ilçe ve semtlerinden D4stattoo\'ya ulaşın. Bağcılar merkezli stüdyomuzda minimal dövme, fine line tattoo, cover-up ve kişiye özel tasarımlar için bölgenizi seçin.',
    'canonical'   => 'istanbul',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1>İstanbul Tattoo Bölgeleri</h1>
        <p class="lead">İstanbul'un neresinde olursanız olun, Bağcılar'daki D4stattoo stüdyosuna kolayca ulaşabilirsiniz. Bölgenizi seçin; oraya özel bilgilere, popüler stillere ve ön görüşme yönlendirmesine göz atın.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="filter-bar" style="margin-bottom:22px">
            <input type="search" id="locSearch" placeholder="İlçe veya semt ara… (örn. Bakırköy, Güneşli)" aria-label="Bölge ara"
                   style="width:min(100%,420px);background:var(--bg-card);border:1px solid var(--line);color:var(--white);border-radius:9px;padding:12px 14px;font-size:.95rem">
        </div>

        <?php if ($featured): ?>
        <div class="section-head"><h2><span>Bağcılar</span> ve yakın bölgeler</h2></div>
        <div class="grid grid-3" id="featuredGrid" style="margin-bottom:40px">
            <?php foreach ($featured as $r): ?>
            <a class="card loc-card" data-name="<?= e(mb_strtolower($r['district'])) ?>" href="<?= e(url('istanbul/' . $r['slug'])) ?>">
                <div class="card-body">
                    <div class="card-title-row"><?= icon('location', 'icon-md') ?><h3><?= e($r['district']) ?> Tattoo</h3></div>
                    <p><?= e(excerpt_of($r['intro'], 100)) ?></p>
                    <span class="card-link"><?= e(t('btn.details')) ?> <?= icon('arrow-right', 'icon-sm') ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section-head"><h2>Tüm İstanbul ilçeleri</h2></div>
        <div class="loc-tags" id="restGrid">
            <?php foreach ($rest as $r): ?>
            <a class="chip chip-link loc-card" data-name="<?= e(mb_strtolower($r['district'])) ?>" href="<?= e(url('istanbul/' . $r['slug'])) ?>">
                <?= icon('location', 'icon-sm') ?> <?= e($r['district']) ?>
            </a>
            <?php endforeach; ?>
        </div>
        <p class="gallery-empty" id="locEmpty" hidden>Aradığınız bölge bulunamadı. Tüm İstanbul'dan bize ulaşabilirsiniz.</p>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2>Bölgenizi bulamadınız mı?</h2>
                <p>Sorun değil — İstanbul'un her yerinden misafir ağırlıyoruz. WhatsApp'tan yazın, dövme fikrinizi birlikte netleştirelim.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp')) ?></a>
                <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var input = document.getElementById('locSearch');
    if (!input) return;
    var cards = document.querySelectorAll('.loc-card');
    var empty = document.getElementById('locEmpty');
    input.addEventListener('input', function () {
        var q = this.value.trim().toLocaleLowerCase('tr');
        var visible = 0;
        cards.forEach(function (c) {
            var show = c.getAttribute('data-name').indexOf(q) !== -1;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (empty) empty.hidden = visible !== 0;
    });
})();
</script>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
