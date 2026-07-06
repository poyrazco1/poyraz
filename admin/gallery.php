<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$catFilter = int_param('cat');
$typeFilter = get_param('type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $row = Database::row('SELECT image, before_image, after_image FROM gallery_items WHERE id = ?', [int_param('id')]);
        if ($row) {
            delete_upload($row['image']);
            delete_upload($row['before_image']);
            delete_upload($row['after_image']);
        }
        Database::delete('gallery_items', int_param('id'));
        flash_set('success', 'Görsel silindi.');
        admin_redirect('gallery.php');
    }

    if ($do === 'save') {
        $type = in_array(post('type'), ['image', 'before_after', 'model'], true) ? post('type') : 'image';
        $data = [
            'category_id' => int_param('category_id') ?: null,
            'title'       => post('title'),
            'type'        => $type,
            'alt_text'    => post('alt_text'),
            'sort_order'  => int_param('sort_order'),
            'status'      => post('status') === '1' ? 1 : 0,
        ];

        $old = $id ? Database::row('SELECT * FROM gallery_items WHERE id = ?', [$id]) : null;

        // Yüklemeler: type=image/model → image; type=before_after → before/after
        foreach (['image' => 'image', 'before_image' => 'before_image', 'after_image' => 'after_image'] as $field => $col) {
            if (!empty($_FILES[$field]['name'])) {
                $up = upload_image($_FILES[$field], 'gallery');
                if ($up['ok'] && $up['path']) {
                    if ($old && $old[$col]) {
                        delete_upload($old[$col]);
                    }
                    $data[$col] = $up['path'];
                } elseif (!$up['ok']) {
                    flash_set('error', 'Görsel: ' . $up['error']);
                    admin_redirect('gallery.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
                }
            }
        }

        // Doğrulama: türe göre zorunlu görseller
        $imageAfter = $data['image'] ?? ($old['image'] ?? null);
        $beforeAfterOk = ($data['before_image'] ?? $old['before_image'] ?? null)
                      && ($data['after_image'] ?? $old['after_image'] ?? null);
        if (($type === 'image' || $type === 'model') && !$imageAfter) {
            flash_set('error', 'Bu tür için bir görsel yüklemelisiniz.');
            admin_redirect('gallery.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }
        if ($type === 'before_after' && !$beforeAfterOk) {
            flash_set('error', 'Önce/Sonra türü için her iki görseli de yüklemelisiniz.');
            admin_redirect('gallery.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }

        if ($id) {
            Database::update('gallery_items', $id, $data);
            flash_set('success', 'Görsel güncellendi.');
        } else {
            $id = Database::insert('gallery_items', $data);
            flash_set('success', 'Görsel eklendi.');
        }
        admin_redirect('gallery.php', ['action' => 'edit', 'id' => $id]);
    }
}

$pageTitle = 'Galeri Yönetimi';
$active = 'gallery';
require __DIR__ . '/partials/header.php';

$cats = Database::all('SELECT * FROM gallery_categories ORDER BY lang, sort_order');

if ($action === 'new' || $action === 'edit'):
    $row = $id ? Database::row('SELECT * FROM gallery_items WHERE id = ?', [$id]) : null;
    if ($action === 'edit' && !$row) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
    $curType = $row['type'] ?? 'image';
?>
<div class="page-head">
    <h1><?= $row ? 'Görseli Düzenle' : 'Yeni Görsel' ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('gallery.php')) ?>">← Listeye Dön</a>
</div>
<form method="post" action="<?= e(admin_url('gallery.php', $id ? ['id' => $id] : [])) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="do" value="save">
    <div class="panel">
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Başlık</label>
                <input id="title" type="text" name="title" maxlength="190" value="<?= e($row['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="alt_text">Alt Metin (SEO)</label>
                <input id="alt_text" type="text" name="alt_text" maxlength="190" value="<?= e($row['alt_text'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id">
                    <option value="">— Kategorisiz —</option>
                    <?php foreach ($cats as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= ($row['category_id'] ?? null) == $c['id'] ? 'selected' : '' ?>>
                        [<?= e(strtoupper($c['lang'])) ?>] <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Tür</label>
                <select id="type" name="type" data-type-select>
                    <option value="image" <?= $curType === 'image' ? 'selected' : '' ?>>Portfolyo Görseli</option>
                    <option value="before_after" <?= $curType === 'before_after' ? 'selected' : '' ?>>Önce / Sonra</option>
                    <option value="model" <?= $curType === 'model' ? 'selected' : '' ?>>Dövme Modeli</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Sıralama</label>
                <input id="sort_order" type="number" name="sort_order" value="<?= (int) ($row['sort_order'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label for="status">Durum</label>
                <select id="status" name="status">
                    <option value="1" <?= ($row['status'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($row['status'] ?? 1) == 0 ? 'selected' : '' ?>>Pasif</option>
                </select>
            </div>
            <div class="form-group" data-single-image>
                <label for="image">Görsel</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <span class="hint">Herhangi bir boyut olabilir; sitede sabit oranlı kart içinde otomatik hizalanır.</span>
                <?php if (!empty($row['image'])): ?>
                <div class="current-img"><img src="<?= e(upload_url($row['image'])) ?>" alt=""></div>
                <?php endif; ?>
            </div>
            <div class="form-group" data-ba-images>
                <label for="before_image">Önce Görseli</label>
                <input id="before_image" type="file" name="before_image" accept=".jpg,.jpeg,.png,.webp">
                <?php if (!empty($row['before_image'])): ?>
                <div class="current-img"><img src="<?= e(upload_url($row['before_image'])) ?>" alt=""></div>
                <?php endif; ?>
                <label for="after_image" style="margin-top:10px">Sonra Görseli</label>
                <input id="after_image" type="file" name="after_image" accept=".jpg,.jpeg,.png,.webp">
                <?php if (!empty($row['after_image'])): ?>
                <div class="current-img"><img src="<?= e(upload_url($row['after_image'])) ?>" alt=""></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </div>
</form>
<script>
(function () {
    var sel = document.querySelector('[data-type-select]');
    var single = document.querySelector('[data-single-image]');
    var ba = document.querySelector('[data-ba-images]');
    function sync() {
        var isBa = sel.value === 'before_after';
        single.style.display = isBa ? 'none' : '';
        ba.style.display = isBa ? '' : 'none';
    }
    sel.addEventListener('change', sync);
    sync();
})();
</script>
<?php else:
    $conds = [];
    $params = [];
    if ($catFilter) { $conds[] = 'g.category_id = ?'; $params[] = $catFilter; }
    if (in_array($typeFilter, ['image', 'before_after', 'model'], true)) { $conds[] = 'g.type = ?'; $params[] = $typeFilter; }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $rows = Database::all(
        "SELECT g.*, c.name AS cat_name FROM gallery_items g
         LEFT JOIN gallery_categories c ON c.id = g.category_id
         $where ORDER BY g.sort_order", $params
    );
    $typeLabels = ['image' => 'Portfolyo', 'before_after' => 'Önce/Sonra', 'model' => 'Model'];
?>
<div class="page-head">
    <div>
        <h1>Galeri</h1>
        <p>Portfolyo görselleri, önce/sonra çiftleri ve dövme modelleri.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(admin_url('gallery.php', ['action' => 'new'])) ?>">+ Yeni Görsel</a>
</div>
<form class="filter-bar" method="get" action="">
    <select name="cat">
        <option value="">Tüm Kategoriler</option>
        <?php foreach ($cats as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>>[<?= e(strtoupper($c['lang'])) ?>] <?= e($c['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="type">
        <option value="">Tüm Türler</option>
        <?php foreach ($typeLabels as $k => $v): ?>
        <option value="<?= $k ?>" <?= $typeFilter === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-sm" type="submit">Filtrele</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Görsel</th><th>Başlık</th><th>Kategori</th><th>Tür</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="7">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><img class="thumb" src="<?= e(upload_url($r['type'] === 'before_after' ? $r['after_image'] : $r['image'])) ?>" alt=""></td>
                <td><strong><?= e($r['title']) ?></strong></td>
                <td><?= e($r['cat_name'] ?? '—') ?></td>
                <td><span class="badge badge-gray"><?= e($typeLabels[$r['type']] ?? $r['type']) ?></span></td>
                <td><?= (int) $r['sort_order'] ?></td>
                <td><?= status_badge((string) $r['status']) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('gallery.php', ['action' => 'edit', 'id' => $r['id']])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(admin_url('gallery.php')) ?>" data-confirm="Bu görsel silinsin mi?">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="do" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Sil</button>
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
