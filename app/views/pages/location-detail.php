<?php
/** İstanbul lokasyon SEO detay sayfası — /istanbul/{slug} */
ensure_location_pages(); // eski DB'de tablo yoksa otomatik oluştur + doldur
try {
    $loc = row_lang('SELECT * FROM location_pages WHERE lang = ? AND slug = ? AND status = 1 LIMIT 1', [$slug]);
} catch (Throwable $e) {
    app_log('location-detail: sorgu hatası: ' . $e->getMessage());
    $loc = null;
}
if (!$loc) {
    http_response_code(404);
    require BASE_PATH . '/app/views/pages/404.php';
    return;
}

$D = $loc['district'];
$services = rows_lang(
    "SELECT title, slug, short_description, icon FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 8"
);
$neighborhoods = array_filter(array_map('trim', explode(',', (string) $loc['neighborhoods'])));

// İlçeye özel WhatsApp mesajı
$waMsg = 'Merhaba D4stattoo, ' . $D . ' çevresinden dövme için bilgi almak istiyorum. '
       . 'Stil, bölge ve ölçü bilgisini paylaşacağım.';

// İlçeye özel SSS (dinamik; ilçe adı geçer) — FAQ schema ile birlikte kullanılır
$faqs = [
    ['question' => $D . ' çevresinden randevu alabilir miyim?',
     'answer'   => 'Elbette. Stüdyomuz Bağcılar\'da olsa da İstanbul\'un her bölgesinden misafir ağırlıyoruz. ' . $D . ' çevresinden randevu formu ya da WhatsApp üzerinden, çoğu zaman aynı gün için bile uygunluk sorabilirsiniz.'],
    ['question' => 'Minimal dövme için ön görüşme gerekiyor mu?',
     'answer'   => 'Küçük ve minimal çalışmalarda bile kısa bir ön görüşme yapıyoruz; böylece tasarımı teninize ve bölgeye en uygun şekilde birlikte netleştiriyoruz. Ön görüşme ücretsizdir ve sizi hiçbir şeye zorunlu bırakmaz.'],
    ['question' => 'Cover-up dövmede fiyat nasıl belirlenir?',
     'answer'   => 'Cover-up fiyatı; kapatılacak dövmenin boyutu ve koyuluğu ile yeni tasarımın kapsamına göre belirlenir. Eski dövmenizin fotoğrafını gönderin, ücretsiz değerlendirme yapıp gerçekçi bir aralık paylaşalım.'],
    ['question' => 'Randevu almadan gelebilir miyim?',
     'answer'   => 'Stüdyomuz randevu sistemiyle çalışır; böylece size kesintisiz zaman ayırabiliyoruz. ' . $D . ' çevresinden geleceğiniz günü önceden belirlemeniz, hem bekleme yaşamamanız hem de tasarımın hazır olması için önemlidir.'],
    ['question' => 'Kol kaplama dövme kaç seans sürer?',
     'answer'   => 'Kol kaplama genellikle 3-6 seans sürer; cilt her seans arasında dinlenir. Toplam seans tahminini ve seans başı aralığını ilk ön görüşmede net olarak paylaşıyoruz.'],
    ['question' => 'Fiyat almak için hangi bilgileri göndermeliyim?',
     'answer'   => 'Aklınızdaki tasarımın referans görselini, yaklaşık ölçüsünü (örn. 10x15 cm), uygulanacak vücut bölgesini ve renkli mi siyah-gri mi istediğinizi paylaşmanız yeterli. Bu bilgilerle size özel ön değerlendirme yapıyoruz.'],
    ['question' => 'Dövme sonrası bakım nasıl yapılır?',
     'answer'   => 'Seans sonunda bölge steril şekilde kapatılır ve size özel bakım talimatları verilir. İlk iki hafta düzenli nemlendirme, kabukları koparmama ve güneşten koruma esastır. Detaylar bakım talimatları sayfamızdadır.'],
];

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => 'İstanbul', 'path' => 'istanbul'],
    ['name' => $D, 'path' => 'istanbul/' . $loc['slug']],
];
$canonicalPath = $loc['canonical_url'] !== '' ? null : 'istanbul/' . $loc['slug'];
// seo_tags() başlığa zaten " | SITE_NAME" ekler; meta_title'daki tekrarı kırp.
$metaTitle = $loc['meta_title'] ?: $loc['h1'];
$metaTitle = preg_replace('/\s*\|\s*' . preg_quote(SITE_NAME, '/') . '\s*$/u', '', $metaTitle);
$meta = [
    'title'       => $metaTitle,
    'description' => $loc['meta_description'] ?: excerpt_of($loc['intro']),
    'canonical'   => $canonicalPath ?? '',
];
if ($loc['canonical_url'] !== '') {
    $meta['canonical'] = $loc['canonical_url']; // tam URL verilmişse aynen kullan
}

// JSON-LD: FAQPage helper mevcut formatı bekliyor (question/answer anahtarları uygun)
$extraHead = jsonld_breadcrumb($breadcrumbs) . jsonld_faq($faqs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e($loc['h1'] ?: ($D . ' Tattoo ve Dövme Stüdyosu')) ?></h1>
        <p class="lead"><?= e($loc['intro']) ?></p>
        <div class="hero-actions" style="margin-top:22px">
            <a class="btn btn-wa" href="<?= e(whatsapp_link($waMsg)) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon-sm') ?> <?= e(t('btn.whatsapp_quote')) ?></a>
            <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= icon('calendar', 'icon-sm') ?> <?= e(t('btn.appointment')) ?></a>
        </div>
    </div>
</section>

<div class="badges">
    <div class="container">
        <ul class="badges-list">
            <li><?= icon('needle', 'icon-sm') ?> <?= e(t('badge.single_needle')) ?></li>
            <li><?= icon('shield', 'icon-sm') ?> <?= e(t('badge.sterile')) ?></li>
            <li><?= icon('sparkle', 'icon-sm') ?> <?= e(t('badge.custom')) ?></li>
            <li><?= icon('user', 'icon-sm') ?> <?= e(t('badge.consult')) ?></li>
            <li><?= icon('clock', 'icon-sm') ?> <?= e(setting('working_hours', t('badge.hours'))) ?></li>
        </ul>
    </div>
</div>

<section class="section">
    <div class="container detail-layout">
        <div>
            <div class="prose"><?= clean_html($loc['content'] ?? '') ?></div>

            <h2 style="margin-top:44px"><?= e($D) ?> çevresinden ilgi gören hizmetler</h2>
            <div class="grid grid-3" style="margin-top:20px">
                <?php foreach (array_slice($services, 0, 6) as $s): ?>
                <article class="card">
                    <div class="card-body">
                        <div class="card-title-row">
                            <?= icon(service_icon_name($s['icon']), 'icon-md') ?>
                            <h3><a href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></h3>
                        </div>
                        <p><?= e(excerpt_of($s['short_description'], 90)) ?></p>
                        <a class="card-link" href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e(t('btn.details')) ?> <?= icon('arrow-right', 'icon-sm') ?></a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if ($neighborhoods): ?>
            <h2 style="margin-top:44px"><?= e($D) ?> ve yakın bölgeler</h2>
            <p style="color:var(--muted)">Aşağıdaki bölgelerden misafirlerimiz de D4stattoo'ya kolayca ulaşıyor:</p>
            <div class="chip-row">
                <?php foreach ($neighborhoods as $n): ?>
                <span class="chip"><?= icon('location', 'icon-sm') ?> <?= e($n) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h2 style="margin-top:44px"><?= e(t('service.faq')) ?></h2>
            <div class="faq-list" style="max-width:none">
                <?php foreach ($faqs as $f): ?>
                <div class="faq-item">
                    <button type="button" class="faq-q"><?= e($f['question']) ?></button>
                    <div class="faq-a"><div class="faq-a-inner"><?= e($f['answer']) ?></div></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="sidebar">
            <div class="side-card side-cta">
                <h3>Dövme fikrini birlikte netleştirelim</h3>
                <p><?= e($D) ?> çevresinden ölçü ve referansınızı paylaşın; size özel ön değerlendirme yapalım.</p>
                <a class="btn btn-wa btn-block" href="<?= e(whatsapp_link($waMsg)) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon-sm') ?> <?= e(t('btn.whatsapp_quote')) ?></a>
                <a class="btn btn-primary btn-block" style="margin-top:10px" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
                <a class="btn btn-outline btn-block" style="margin-top:10px" href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a>
            </div>
            <div class="side-card">
                <h3>Diğer İstanbul bölgeleri</h3>
                <ul>
                    <?php
                    $others = Database::all(
                        "SELECT district, slug FROM location_pages WHERE lang = 'tr' AND status = 1 AND slug != ? ORDER BY sort_order LIMIT 10",
                        [$loc['slug']]
                    );
                    foreach ($others as $o): ?>
                    <li><a href="<?= e(url('istanbul/' . $o['slug'])) ?>"><?= e($o['district']) ?> Tattoo</a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= e(url('istanbul')) ?>" style="color:var(--red);font-weight:700">Tüm bölgeler →</a></li>
                </ul>
            </div>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="cta-band">
            <div>
                <h2>Dövme fikrini birlikte netleştirelim</h2>
                <p><?= e($D) ?> çevresinden ulaşın; minimal, fine line, realistic ya da cover-up — tasarımınızı ücretsiz ön görüşmeyle planlayalım.</p>
            </div>
            <div class="cta-actions">
                <a class="btn btn-wa" href="<?= e(whatsapp_link($waMsg)) ?>" target="_blank" rel="noopener"><?= e(t('btn.whatsapp_quote')) ?></a>
                <a class="btn btn-primary" href="<?= e(url('randevu-al')) ?>"><?= e(t('btn.appointment')) ?></a>
            </div>
        </div>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
