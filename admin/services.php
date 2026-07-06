<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$langFilter = preg_replace('/[^a-z]/', '', get_param('lang'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $row = Database::row('SELECT image FROM services WHERE id = ?', [int_param('id')]);
        if ($row) {
            delete_upload($row['image']);
        }
        Database::delete('services', int_param('id'));
        flash_set('success', 'Hizmet silindi.');
        admin_redirect('services.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'              => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'title'             => post('title'),
            'short_description' => post('short_description'),
            'content'           => clean_html((string) ($_POST['content'] ?? '')),
            'icon'              => post('icon'),
            'sort_order'        => int_param('sort_order'),
            'meta_title'        => post('meta_title'),
            'meta_description'  => post('meta_description'),
            'status'            => post('status') === '1' ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Başlık zorunludur.');
            admin_redirect('services.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }
        $slug = post('slug') !== '' ? slugify(post('slug')) : slugify($data['title']);
        $data['slug'] = unique_slug('services', $slug, $data['lang'], $id);

        if (!empty($_FILES['image']['name'])) {
            $up = upload_image($_FILES['image'], 'services');
            if ($up['ok'] && $up['path']) {
                if ($id) {
                    $old = Database::row('SELECT image FROM services WHERE id = ?', [$id]);
                    if ($old) delete_upload($old['image']);
                }
                $data['image'] = $up['path'];
            } elseif (!$up['ok']) {
                flash_set('error', 'Görsel: ' . $up['error']);
                admin_redirect('services.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
            }
        }

        if ($id) {
            Database::update('services', $id, $data);
            flash_set('success', 'Hizmet güncellendi.');
        } else {
            $id = Database::insert('services', $data);
            flash_set('success', 'Hizmet eklendi.');
        }
        admin_redirect('services.php', ['action' => 'edit', 'id' => $id]);
    }
}

$pageTitle = 'Hizmet Yönetimi';
$active = 'services';
require __DIR__ . '/partials/header.php';

if ($action === 'new' || $action === 'edit'):
    $row = $id ? Database::row('SELECT * FROM services WHERE id = ?', [$id]) : null;
    if ($action === 'edit' && !$row) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
?>
<div class="page-head">
    <h1><?= $row ? 'Hizmeti Düzenle' : 'Yeni Hizmet' ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('services.php')) ?>">← Listeye Dön</a>
</div>
<form method="post" action="<?= e(admin_url('services.php', $id ? ['id' => $id] : [])) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="do" value="save">
    <div class="panel">
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Hizmet Adı *</label>
                <input id="title" type="text" name="title" required maxlength="190" value="<?= e($row['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input id="slug" type="text" name="slug" maxlength="190" value="<?= e($row['slug'] ?? '') ?>" placeholder="/hizmetler/{slug}">
            </div>
            <div class="form-group">
                <label for="lang">Dil</label>
                <?= lang_select('lang', $row['lang'] ?? 'tr') ?>
            </div>
            <div class="form-group">
                <label for="status">Durum</label>
                <select id="status" name="status">
                    <option value="1" <?= ($row['status'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= ($row['status'] ?? 1) == 0 ? 'selected' : '' ?>>Pasif</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Sıralama</label>
                <input id="sort_order" type="number" name="sort_order" value="<?= (int) ($row['sort_order'] ?? 0) ?>">
            </div>
            <div class="form-group">
                <label for="icon">İkon Kodu (opsiyonel)</label>
                <input id="icon" type="text" name="icon" maxlength="50" value="<?= e($row['icon'] ?? '') ?>">
            </div>
            <div class="form-group full">
                <label for="short_description">Kısa Açıklama</label>
                <textarea id="short_description" name="short_description" maxlength="500" style="min-height:70px"><?= e($row['short_description'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label for="content">Detay İçerik (HTML)</label>
                <textarea id="content" name="content" class="editor"><?= e($row['content'] ?? '') ?></textarea>
                <span class="hint">Önerilen bölümler: Bu Stil Nedir? / Kimler İçin Uygundur? / Ortalama Süreç / İyileşme ve Bakım / Fiyat Nasıl Belirlenir? (h2 başlıklarıyla)</span>
            </div>
            <div class="form-group">
                <label for="image">Görsel</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                <?php if (!empty($row['image'])): ?>
                <div class="current-img"><img src="<?= e(upload_url($row['image'])) ?>" alt=""></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="meta_title">Meta Title (SEO)</label>
                <input id="meta_title" type="text" name="meta_title" maxlength="190" value="<?= e($row['meta_title'] ?? '') ?>">
                <label for="meta_description" style="margin-top:10px">Meta Description (SEO)</label>
                <input id="meta_description" type="text" name="meta_description" maxlength="300" value="<?= e($row['meta_description'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <?php if ($row): ?>
            <a class="btn btn-ghost" href="<?= e(url('hizmetler/' . $row['slug'], $row['lang'])) ?>" target="_blank">Sayfayı Gör ↗</a>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php else:
    $where = $langFilter ? ' WHERE lang = ' . Database::pdo()->quote($langFilter) : '';
    $rows = Database::all("SELECT * FROM services $where ORDER BY lang, sort_order");
?>
<div class="page-head">
    <div>
        <h1>Hizmetler</h1>
        <p>Her hizmet, /hizmetler/{slug} adresinde ayrı bir SEO sayfası olarak yayınlanır.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(admin_url('services.php', ['action' => 'new'])) ?>">+ Yeni Hizmet</a>
</div>
<form class="filter-bar" method="get" action="">
    <?= lang_select('lang', $langFilter, true) ?>
    <button class="btn btn-sm" type="submit">Filtrele</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Görsel</th><th>Hizmet</th><th>Slug</th><th>Dil</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="7">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><img class="thumb" src="<?= e(upload_url($r['image'])) ?>" alt=""></td>
                <td><strong><?= e($r['title']) ?></strong></td>
                <td>/hizmetler/<?= e($r['slug']) ?></td>
                <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                <td><?= (int) $r['sort_order'] ?></td>
                <td><?= status_badge((string) $r['status']) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('services.php', ['action' => 'edit', 'id' => $r['id']])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(admin_url('services.php')) ?>" data-confirm="Bu hizmet silinsin mi?">
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
