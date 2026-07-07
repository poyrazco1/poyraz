<?php
require_once __DIR__ . '/partials/top.php';

$lang = preg_replace('/[^a-z]/', '', get_param('lang', 'tr')) ?: 'tr';

// Yönetilebilir ayar tanımları: key => [etiket, tip, ipucu]
$fields = [
    'site_name'        => ['Site Adı', 'text', ''],
    'site_slogan'      => ['Slogan', 'text', 'Ana sayfa başlık etiketinde kullanılır'],
    'site_description' => ['Site Açıklaması (SEO)', 'textarea', 'Meta description olarak kullanılır'],
    'phone'            => ['Telefon', 'text', 'Görünen format: +90 505 801 61 26'],
    'whatsapp_number'  => ['WhatsApp Numarası', 'text', 'Uluslararası format, + olmadan: 905058016126'],
    'instagram_url'    => ['Instagram Adresi', 'url', ''],
    'working_hours'    => ['Çalışma Saatleri', 'text', ''],
    'location_text'    => ['Lokasyon Metni', 'text', 'Örn: İstanbul / Bağcılar (açık adres yazmayın)'],
    'experience_years' => ['Deneyim (yıl)', 'text', ''],
    'hero_title'       => ['Hero Başlık', 'text', 'Ana sayfadaki büyük başlık'],
    'hero_subtitle'    => ['Hero Alt Metin', 'textarea', ''],
    'hero_btn_primary' => ['Hero Buton 1 Yazısı', 'text', ''],
    'hero_btn_secondary' => ['Hero Buton 2 Yazısı', 'text', ''],
    'footer_text'      => ['Footer Metni', 'textarea', ''],
    'artist_name'      => ['Sanatçı Adı', 'text', ''],
    'artist_bio'       => ['Sanatçı Tanıtımı', 'textarea', ''],
    'primary_color'    => ['Ana Renk (opsiyonel)', 'color', 'Varsayılan: #e11d2e'],
    'campaign_show'    => ['Kampanya Bandı (1=göster, 0=gizle)', 'text', ''],
    'popup_show'       => ['Pop-up Teklif Formu (1=göster, 0=gizle)', 'text', ''],
    'google_reviews_url' => ['Google Yorumlar Linki', 'url', 'Yorumlar bölümündeki kaynak bağlantısı için'],
    'google_site_verification' => ['Google Search Console Doğrulama Kodu', 'text', 'Yalnızca içerik değeri (content="..." içindeki kod). Tüm sayfaların <head> bölümüne eklenir.'],
];
$imageFields = [
    'logo'         => ['Logo', 'PNG/SVG önerilir, koyu zeminde görünecek'],
    'favicon'      => ['Favicon', 'ICO, PNG veya SVG'],
    'og_image'     => ['Sosyal Medya Paylaşım Görseli (OG)', '1200×630 önerilir'],
    'artist_image' => ['Sanatçı Fotoğrafı', 'Dikey (4:5) önerilir'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lang = preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr';

    foreach ($fields as $key => [$label, $type]) {
        if (isset($_POST[$key])) {
            set_setting($key, trim((string) $_POST[$key]), $lang, $type);
        }
    }
    // Görsel yüklemeleri (dil bağımsız → tr kaydına yazılır)
    foreach ($imageFields as $key => $def) {
        if (!empty($_FILES[$key]['name'])) {
            $up = upload_image($_FILES[$key], 'settings', false, $key === 'favicon' || $key === 'logo');
            if ($up['ok'] && $up['path']) {
                delete_upload(setting($key, '', 'tr'));
                set_setting($key, $up['path'], 'tr', 'image');
            } elseif (!$up['ok']) {
                flash_set('error', $def[0] . ': ' . $up['error']);
            }
        }
        // Görseli kaldır
        if (post('remove_' . $key) === '1') {
            delete_upload(setting($key, '', 'tr'));
            set_setting($key, '', 'tr', 'image');
        }
    }
    flash_set('success', 'Ayarlar kaydedildi.');
    admin_redirect('settings.php', ['lang' => $lang]);
}

$pageTitle = 'Site Ayarları';
$active = 'settings';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Site Ayarları</h1>
        <p>Logo, iletişim bilgileri ve ön yüz metinleri. Dil bazlı içerikler için üstteki dil seçimini kullanın.</p>
    </div>
    <form method="get" action="">
        <?= lang_select('lang', $lang) ?>
        <noscript><button class="btn btn-sm" type="submit">Değiştir</button></noscript>
    </form>
</div>

<form method="post" action="" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="lang" value="<?= e($lang) ?>">

    <div class="panel">
        <h2>Metin Ayarları — <?= e(strtoupper($lang)) ?></h2>
        <div class="form-grid">
            <?php foreach ($fields as $key => [$label, $type, $hint]):
                $value = all_settings()[$lang][$key] ?? '';
            ?>
            <div class="form-group <?= $type === 'textarea' ? 'full' : '' ?>">
                <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
                <?php if ($type === 'textarea'): ?>
                <textarea id="s-<?= e($key) ?>" name="<?= e($key) ?>"><?= e($value) ?></textarea>
                <?php elseif ($type === 'color'): ?>
                <input id="s-<?= e($key) ?>" type="color" name="<?= e($key) ?>" value="<?= e($value ?: '#e11d2e') ?>">
                <?php else: ?>
                <input id="s-<?= e($key) ?>" type="text" name="<?= e($key) ?>" value="<?= e($value) ?>">
                <?php endif; ?>
                <?php if ($hint): ?><span class="hint"><?= e($hint) ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="panel">
        <h2>Görseller (tüm diller için ortak)</h2>
        <div class="form-grid">
            <?php foreach ($imageFields as $key => [$label, $hint]):
                $current = setting($key, '', 'tr');
            ?>
            <div class="form-group">
                <label for="s-<?= e($key) ?>"><?= e($label) ?></label>
                <input id="s-<?= e($key) ?>" type="file" name="<?= e($key) ?>" accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                <span class="hint"><?= e($hint) ?></span>
                <?php if ($current): ?>
                <div class="current-img">
                    <img src="<?= e(upload_url($current)) ?>" alt="<?= e($label) ?>">
                    <label style="font-weight:400;font-size:.8rem"><input type="checkbox" name="remove_<?= e($key) ?>" value="1"> Kaldır</label>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Kaydet</button>
    </div>
</form>
<script>
document.getElementById('lang').addEventListener('change', function () { this.form.submit(); });
</script>
<?php require __DIR__ . '/partials/footer.php'; ?>
