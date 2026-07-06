<?php
require_once __DIR__ . '/partials/top.php';

ensure_location_pages(); // eski DB'de tablo yoksa otomatik oluştur + doldur

$action = get_param('action', 'list');
$id = int_param('id');
$q = get_param('q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('location_pages', int_param('id'));
        flash_set('success', 'Lokasyon sayfası silindi.');
        admin_redirect('location-pages.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'             => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'city'             => post('city') ?: 'İstanbul',
            'district'         => post('district'),
            'h1'               => post('h1'),
            'intro'            => post('intro'),
            'content'          => clean_html((string) ($_POST['content'] ?? '')),
            'neighborhoods'    => post('neighborhoods'),
            'seo_keywords'     => post('seo_keywords'),
            'meta_title'       => post('meta_title'),
            'meta_description' => post('meta_description'),
            'canonical_url'    => post('canonical_url'),
            'sort_order'       => int_param('sort_order'),
            'status'           => post('status') === '1' ? 1 : 0,
            'title'            => post('district') . ' Tattoo | ' . SITE_NAME,
        ];
        if ($data['district'] === '') {
            flash_set('error', 'İlçe/semt adı zorunludur.');
            admin_redirect('location-pages.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }
        $slug = post('slug') !== '' ? slugify(post('slug')) : slugify($data['district'] . '-tattoo');
        $data['slug'] = unique_slug('location_pages', $slug, $data['lang'], $id);

        if ($id) {
            Database::update('location_pages', $id, $data);
            flash_set('success', 'Lokasyon sayfası güncellendi.');
        } else {
            $id = Database::insert('location_pages', $data);
            flash_set('success', 'Lokasyon sayfası eklendi.');
        }
        admin_redirect('location-pages.php', ['action' => 'edit', 'id' => $id]);
    }
}

$pageTitle = 'Lokasyon SEO Sayfaları';
$active = 'location-pages';
require __DIR__ . '/partials/header.php';

if ($action === 'new' || $action === 'edit'):
    $row = $id ? Database::row('SELECT * FROM location_pages WHERE id = ?', [$id]) : null;
    if ($action === 'edit' && !$row) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
?>
<div class="page-head">
    <h1><?= $row ? 'Lokasyon Sayfasını Düzenle' : 'Yeni Lokasyon Sayfası' ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('location-pages.php')) ?>">← Listeye Dön</a>
</div>
<form method="post" action="<?= e(admin_url('location-pages.php', $id ? ['id' => $id] : [])) ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="do" value="save">
    <div class="panel">
        <div class="form-grid">
            <div class="form-group">
                <label for="district">İlçe / Semt *</label>
                <input id="district" type="text" name="district" required maxlength="120" value="<?= e($row['district'] ?? '') ?>" placeholder="Örn: Bağcılar, Güneşli">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input id="slug" type="text" name="slug" maxlength="190" value="<?= e($row['slug'] ?? '') ?>" placeholder="/istanbul/{slug}">
            </div>
            <div class="form-group">
                <label for="city">Şehir</label>
                <input id="city" type="text" name="city" maxlength="80" value="<?= e($row['city'] ?? 'İstanbul') ?>">
            </div>
            <div class="form-group">
                <label for="lang">Dil</label>
                <?= lang_select('lang', $row['lang'] ?? 'tr') ?>
            </div>
            <div class="form-group">
                <label for="sort_order">Sıralama (küçük = önce)</label>
                <input id="sort_order" type="number" name="sort_order" value="<?= (int) ($row['sort_order'] ?? 100) ?>">
                <span class="hint">100'den küçük değerler /istanbul sayfasında "öne çıkan" olarak listelenir.</span>
            </div>
            <div class="form-group">
                <label for="status">Durum</label>
                <select id="status" name="status">
                    <option value="1" <?= ($row['status'] ?? 1) == 1 ? 'selected' : '' ?>>Yayında</option>
                    <option value="0" <?= ($row['status'] ?? 1) == 0 ? 'selected' : '' ?>>Taslak</option>
                </select>
            </div>
            <div class="form-group full">
                <label for="h1">H1 Başlık</label>
                <input id="h1" type="text" name="h1" maxlength="190" value="<?= e($row['h1'] ?? '') ?>" placeholder="Örn: Bağcılar Tattoo ve Dövme Stüdyosu">
            </div>
            <div class="form-group full">
                <label for="intro">Giriş Metni (hero altı, kısa)</label>
                <textarea id="intro" name="intro" style="min-height:70px"><?= e($row['intro'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label for="content">İçerik (HTML) — buradan genişletebilirsiniz</label>
                <textarea id="content" name="content" class="editor"><?= e($row['content'] ?? '') ?></textarea>
                <span class="hint">Kalite için: ilçeye özel giriş, stüdyo seçimi, fiyat, hijyen ve bakım başlıkları (h2 ile). Kopya içerikten kaçının.</span>
            </div>
            <div class="form-group full">
                <label for="neighborhoods">Yakın Bölgeler (virgülle)</label>
                <input id="neighborhoods" type="text" name="neighborhoods" maxlength="500" value="<?= e($row['neighborhoods'] ?? '') ?>" placeholder="Güneşli, Mahmutbey, Kirazlı">
            </div>
            <div class="form-group">
                <label for="meta_title">Meta Title (SEO)</label>
                <input id="meta_title" type="text" name="meta_title" maxlength="190" value="<?= e($row['meta_title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="meta_description">Meta Description (SEO)</label>
                <input id="meta_description" type="text" name="meta_description" maxlength="300" value="<?= e($row['meta_description'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="seo_keywords">SEO Anahtar Kelimeler</label>
                <input id="seo_keywords" type="text" name="seo_keywords" maxlength="400" value="<?= e($row['seo_keywords'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="canonical_url">Canonical URL (opsiyonel)</label>
                <input id="canonical_url" type="text" name="canonical_url" maxlength="255" value="<?= e($row['canonical_url'] ?? '') ?>" placeholder="Boş = kendi URL'i">
                <span class="hint">Yalnızca bu sayfa başka bir sayfanın kopyasıysa doldurun; sitemap dışı bırakılır.</span>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <?php if ($row): ?>
            <a class="btn btn-ghost" href="<?= e(url('istanbul/' . $row['slug'], $row['lang'])) ?>" target="_blank">Sayfayı Gör ↗</a>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php else:
    $conds = [];
    $params = [];
    if ($q !== '') { $conds[] = '(district LIKE ? OR slug LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $rows = Database::all("SELECT * FROM location_pages $where ORDER BY sort_order, district", $params);
    $total = count($rows);
?>
<div class="page-head">
    <div>
        <h1>Lokasyon SEO Sayfaları</h1>
        <p><?= $total ?> sayfa · Her biri <code>/istanbul/{slug}</code> adresinde yayınlanır ve sitemap'e eklenir.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(admin_url('location-pages.php', ['action' => 'new'])) ?>"><?= icon('plus', 'icon-sm') ?> Yeni Lokasyon</a>
</div>
<form class="filter-bar" method="get" action="">
    <input type="text" name="q" placeholder="İlçe veya slug ara…" value="<?= e($q) ?>">
    <button class="btn btn-sm" type="submit"><?= icon('search', 'icon-sm') ?> Ara</button>
    <a class="btn btn-sm btn-ghost" href="<?= e(url('istanbul')) ?>" target="_blank">Landing'i Gör ↗</a>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>İlçe / Semt</th><th>Slug</th><th>Meta Title</th><th>Sıra</th><th>Durum</th><th>Güncelleme</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="8">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= (int) $r['id'] ?></td>
                <td><strong><?= e($r['district']) ?></strong><?= (int) $r['sort_order'] < 100 ? ' <span class="badge badge-red">Öne çıkan</span>' : '' ?></td>
                <td>/istanbul/<?= e($r['slug']) ?></td>
                <td><?= e(excerpt_of($r['meta_title'] ?: '', 40)) ?></td>
                <td><?= (int) $r['sort_order'] ?></td>
                <td><?= status_badge((string) $r['status']) ?></td>
                <td><?= e(format_date($r['updated_at'])) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('location-pages.php', ['action' => 'edit', 'id' => $r['id']])) ?>"><?= icon('edit', 'icon-sm') ?> Düzenle</a>
                        <form method="post" action="<?= e(admin_url('location-pages.php')) ?>" data-confirm="Bu lokasyon sayfası silinsin mi?">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="do" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit"><?= icon('delete', 'icon-sm') ?> Sil</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
