<?php
require_once __DIR__ . '/partials/top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'toggle') {
        $lg = Database::row('SELECT * FROM languages WHERE id = ?', [int_param('id')]);
        if ($lg) {
            if ($lg['code'] === DEFAULT_LANGUAGE && (int) $lg['is_active'] === 1) {
                flash_set('error', 'Varsayılan dil (TR) pasif yapılamaz.');
            } else {
                Database::run('UPDATE languages SET is_active = 1 - is_active WHERE id = ?', [$lg['id']]);
                flash_set('success', $lg['name'] . ' dili ' . ($lg['is_active'] ? 'pasif' : 'aktif') . ' yapıldı.');
            }
        }
        admin_redirect('languages.php');
    }
}

$rows = Database::all('SELECT * FROM languages ORDER BY sort_order');
$counts = [];
foreach (['pages', 'services', 'blog_posts', 'faq_items', 'price_list_items'] as $tbl) {
    foreach (Database::all("SELECT lang, COUNT(*) AS c FROM `$tbl` GROUP BY lang") as $r) {
        $counts[$r['lang']][$tbl] = (int) $r['c'];
    }
}

$pageTitle = 'Dil Yönetimi';
$active = 'languages';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Dil Yönetimi</h1>
        <p>TR varsayılan dildir ve kapatılamaz. AR için RTL desteği otomatik uygulanır.
           Bir dilde içerik yoksa sitede TR içeriği gösterilir (fallback).</p>
    </div>
</div>
<div class="table-wrap">
    <table>
        <thead><tr><th>Kod</th><th>Dil</th><th>Yön</th><th>Sayfa</th><th>Hizmet</th><th>Blog</th><th>SSS</th><th>Fiyat</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $c = $counts[$r['code']] ?? []; ?>
            <tr>
                <td><span class="badge badge-blue"><?= e(strtoupper($r['code'])) ?></span></td>
                <td><strong><?= e($r['name']) ?></strong><?= $r['code'] === DEFAULT_LANGUAGE ? ' <span class="badge badge-red">Varsayılan</span>' : '' ?></td>
                <td><?= $r['direction'] === 'rtl' ? 'Sağdan sola (RTL)' : 'Soldan sağa' ?></td>
                <td><?= $c['pages'] ?? 0 ?></td>
                <td><?= $c['services'] ?? 0 ?></td>
                <td><?= $c['blog_posts'] ?? 0 ?></td>
                <td><?= $c['faq_items'] ?? 0 ?></td>
                <td><?= $c['price_list_items'] ?? 0 ?></td>
                <td><?= status_badge((string) $r['is_active']) ?></td>
                <td>
                    <form method="post" action="<?= e(admin_url('languages.php')) ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="do" value="toggle">
                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <button class="btn btn-sm <?= $r['is_active'] ? 'btn-danger' : 'btn-primary' ?>" type="submit">
                            <?= $r['is_active'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="panel" style="margin-top:22px">
    <h2>Arayüz Çevirileri</h2>
    <p style="color:var(--muted);font-size:.88rem">
        Menü, buton ve form metinleri <code>app/lang/tr.php</code>, <code>en.php</code>, <code>ar.php</code>, <code>ru.php</code>
        dosyalarında düzenli anahtar-değer yapısıyla tutulur. Yeni metin eklemek için ilgili dosyaya anahtar ekleyin;
        eksik anahtarlar otomatik olarak TR'den okunur. İçerikler (sayfa, hizmet, blog, SSS, fiyat) ise
        ilgili modüllerdeki dil seçimiyle her dil için ayrı ayrı yönetilir.
    </p>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
