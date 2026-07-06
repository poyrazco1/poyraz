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
foreach (Database::all("SELECT slug, lang, updated_at FROM pages WHERE status = 1 AND slug NOT IN ('hakkimizda','hijyen','bakim-talimatlari','kvkk')") as $r) {
    $urls[] = ['loc' => url($r['slug'], $r['lang']), 'lastmod' => $r['updated_at'], 'priority' => '0.5'];
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
