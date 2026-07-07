<?php
/** Dinamik sitemap.xml — tüm diller ve içerikler */
header('Content-Type: application/xml; charset=utf-8');

$urls = [];
$staticPaths = [
    '', 'hakkimizda', 'sanatci', 'hizmetler', 'galeri', 'once-sonra', 'hijyen',
    'dovme-modelleri', 'randevu-al', 'fiyat-teklifi-al', 'fiyat-listesi', 'sss',
    'blog', 'akademi', 'bakim-talimatlari', 'iletisim', 'kvkk',
];
$langs = active_language_codes();

foreach ($staticPaths as $p) {
    foreach ($langs as $l) {
        $urls[] = ['loc' => url($p, $l), 'priority' => $p === '' ? '1.0' : '0.7'];
    }
}
foreach (Database::all('SELECT slug, lang, updated_at FROM services WHERE status = 1') as $r) {
    $urls[] = ['loc' => url('hizmetler/' . $r['slug'], $r['lang']), 'lastmod' => $r['updated_at'], 'priority' => '0.8'];
}
foreach (Database::all('SELECT slug, lang, updated_at FROM blog_posts WHERE status = 1') as $r) {
    $urls[] = ['loc' => url('blog/' . $r['slug'], $r['lang']), 'lastmod' => $r['updated_at'], 'priority' => '0.6'];
}
ensure_seo_pages(); // yeni SEO/rehber sayfaları sitemap'e girmeden önce hazır olsun
$locationSlugs = ['bagcilar-tattoo', 'gunesli-tattoo'];             // yerel SEO → 0.9
$guideSlugs = ['minimal-dovme', 'yazi-dovmesi', 'ilk-dovme-rehberi', 'dovme-bakimi', 'dovme-fiyatlari']; // hizmet/rehber → 0.8
foreach (Database::all("SELECT slug, lang, updated_at FROM pages WHERE status = 1 AND slug NOT IN ('hakkimizda','hijyen','bakim-talimatlari','kvkk')") as $r) {
    if (in_array($r['slug'], $locationSlugs, true)) {
        $priority = '0.9';
    } elseif (in_array($r['slug'], $guideSlugs, true)) {
        $priority = '0.8';
    } else {
        $priority = '0.5';
    }
    $urls[] = ['loc' => url($r['slug'], $r['lang']), 'lastmod' => $r['updated_at'], 'priority' => $priority];
}
// İstanbul landing + lokasyon SEO sayfaları (tablo yoksa güvenli atla)
$urls[] = ['loc' => url('istanbul'), 'priority' => '0.7'];
try {
    ensure_location_pages();
    // Kök SEO sayfası olan slug'lar (canonical oraya gider) → sitemap'te tekrar etme
    $rootTwins = array_column(
        Database::all("SELECT slug FROM pages WHERE lang = 'tr' AND status = 1"), 'slug'
    );
    foreach (Database::all("SELECT slug, lang, updated_at, canonical_url FROM location_pages WHERE status = 1") as $r) {
        if (trim((string) $r['canonical_url']) !== '' || in_array($r['slug'], $rootTwins, true)) {
            continue; // farklı canonical veya kök ikizi varsa sitemap'e ekleme
        }
        $urls[] = ['loc' => url('istanbul/' . $r['slug'], $r['lang']), 'lastmod' => $r['updated_at'], 'priority' => '0.6'];
    }
} catch (Throwable $e) {
    app_log('sitemap: location_pages atlandı: ' . $e->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n    <loc>" . e($u['loc']) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
    }
    echo '    <priority>' . ($u['priority'] ?? '0.5') . "</priority>\n  </url>\n";
}
echo '</urlset>';
