<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');
$langFilter = preg_replace('/[^a-z]/', '', get_param('lang'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('price_list_items', int_param('id'));
        flash_set('success', 'Fiyat kalemi silindi.');
        admin_redirect('prices.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'        => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'title'       => post('title'),
            'description' => clean_html((string) ($_POST['description'] ?? '')),
            'price_text'  => post('price_text'),
            'sort_order'  => int_param('sort_order'),
            'status'      => post('status') === '1' ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Başlık zorunludur.');
            admin_redirect('prices.php', $id ? ['id' => $id] : []);
        }
        if ($id) {
            Database::update('price_list_items', $id, $data);
            flash_set('success', 'Fiyat kalemi güncellendi.');
        } else {
            Database::insert('price_list_items', $data);
            flash_set('success', 'Fiyat kalemi eklendi.');
        }
        admin_redirect('prices.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM price_list_items WHERE id = ?', [$id]) : null;
$where = $langFilter ? ' WHERE lang = ' . Database::pdo()->quote($langFilter) : '';
$rows = Database::all("SELECT * FROM price_list_items $where ORDER BY lang, sort_order");

$pageTitle = 'Fiyat Listesi';
$active = 'prices';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Fiyat Listesi</h1>
        <p>Örnek başlangıç fiyatları. "Kesin fiyat için ön değerlendirme gerekir" notu sitede otomatik gösterilir.</p>
    </div>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Kalemi Düzenle' : 'Yeni Kalem' ?></h2>
        <form method="post" action="<?= e(admin_url('prices.php', $edit ? ['id' => $edit['id']] : [])) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="title">Başlık *</label>
                    <input id="title" type="text" name="title" required maxlength="190" value="<?= e($edit['title'] ?? '') ?>">
                </div>
                <div class="form-group full">
                    <label for="description">Açıklama (HTML destekler)</label>
                    <textarea id="description" name="description"><?= e($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="price_text">Fiyat Metni</label>
                    <input id="price_text" type="text" name="price_text" maxlength="100"
                           placeholder="Örn: 1.500 ₺'den başlayan örnek fiyat" value="<?= e($edit['price_text'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="lang">Dil</label>
                    <?= lang_select('lang', $edit['lang'] ?? 'tr') ?>
                </div>
                <div class="form-group">
                    <label for="sort_order">Sıralama</label>
                    <input id="sort_order" type="number" name="sort_order" value="<?= (int) ($edit['sort_order'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label for="status">Durum</label>
                    <select id="status" name="status">
                        <option value="1" <?= ($edit['status'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= ($edit['status'] ?? 1) == 0 ? 'selected' : '' ?>>Pasif</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Güncelle' : 'Ekle' ?></button>
                <?php if ($edit): ?><a class="btn btn-ghost" href="<?= e(admin_url('prices.php')) ?>">Vazgeç</a><?php endif; ?>
            </div>
        </form>
    </div>
    <div>
        <form class="filter-bar" method="get" action="">
            <?= lang_select('lang', $langFilter, true) ?>
            <button class="btn btn-sm" type="submit"><?= icon('filter', 'icon-sm') ?> Filtrele</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Başlık</th><th>Fiyat</th><th>Dil</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php if (!$rows): ?><tr class="empty-row"><td colspan="6">Kayıt yok.</td></tr><?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><strong><?= e($r['title']) ?></strong></td>
                        <td><?= e($r['price_text']) ?></td>
                        <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                        <td><?= (int) $r['sort_order'] ?></td>
                        <td><?= status_badge((string) $r['status']) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('prices.php', ['id' => $r['id']])) ?>"><?= icon('edit', 'icon-sm') ?> Düzenle</a>
                                <form method="post" action="<?= e(admin_url('prices.php')) ?>" data-confirm="Kalem silinsin mi?">
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
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
