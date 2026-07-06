<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');
$sources = ['google' => 'Google', 'instagram' => 'Instagram', 'site' => 'Site'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $row = Database::row('SELECT image FROM testimonials WHERE id = ?', [int_param('id')]);
        if ($row) {
            delete_upload($row['image']);
        }
        Database::delete('testimonials', int_param('id'));
        flash_set('success', 'Yorum silindi.');
        admin_redirect('testimonials.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'    => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'name'    => post('name'),
            'source'  => array_key_exists(post('source'), $sources) ? post('source') : 'google',
            'rating'  => min(5, max(1, int_param('rating', 5))),
            'comment' => post('comment'),
            'status'  => post('status') === '1' ? 1 : 0,
        ];
        if ($data['name'] === '' || $data['comment'] === '') {
            flash_set('error', 'Ad ve yorum alanları zorunludur.');
            admin_redirect('testimonials.php', $id ? ['id' => $id] : []);
        }
        if (!empty($_FILES['image']['name'])) {
            $up = upload_image($_FILES['image'], 'gallery');
            if ($up['ok'] && $up['path']) {
                $old = $id ? Database::row('SELECT image FROM testimonials WHERE id = ?', [$id]) : null;
                if ($old) delete_upload($old['image']);
                $data['image'] = $up['path'];
            } elseif (!$up['ok']) {
                flash_set('error', 'Görsel: ' . $up['error']);
                admin_redirect('testimonials.php', $id ? ['id' => $id] : []);
            }
        }
        if ($id) {
            Database::update('testimonials', $id, $data);
            flash_set('success', 'Yorum güncellendi.');
        } else {
            Database::insert('testimonials', $data);
            flash_set('success', 'Yorum eklendi.');
        }
        admin_redirect('testimonials.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM testimonials WHERE id = ?', [$id]) : null;
$rows = Database::all('SELECT * FROM testimonials ORDER BY lang, id DESC');

$pageTitle = 'Yorumlar';
$active = 'testimonials';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Müşteri Yorumları</h1>
        <p>Google ve Instagram yorumlarını buradan ekleyip ana sayfada gösterebilirsiniz.</p>
    </div>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Yorumu Düzenle' : 'Yeni Yorum' ?></h2>
        <form method="post" action="<?= e(admin_url('testimonials.php', $edit ? ['id' => $edit['id']] : [])) ?>" enctype="multipart/form-data">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Ad *</label>
                    <input id="name" type="text" name="name" required maxlength="150" value="<?= e($edit['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="source">Kaynak</label>
                    <select id="source" name="source">
                        <?php foreach ($sources as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($edit['source'] ?? 'google') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="rating">Puan (1-5)</label>
                    <input id="rating" type="number" name="rating" min="1" max="5" value="<?= (int) ($edit['rating'] ?? 5) ?>">
                </div>
                <div class="form-group">
                    <label for="lang">Dil</label>
                    <?= lang_select('lang', $edit['lang'] ?? 'tr') ?>
                </div>
                <div class="form-group full">
                    <label for="comment">Yorum *</label>
                    <textarea id="comment" name="comment" required><?= e($edit['comment'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Görsel (opsiyonel)</label>
                    <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
                    <?php if (!empty($edit['image'])): ?>
                    <div class="current-img"><img src="<?= e(upload_url($edit['image'])) ?>" alt=""></div>
                    <?php endif; ?>
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
                <?php if ($edit): ?><a class="btn btn-ghost" href="<?= e(admin_url('testimonials.php')) ?>">Vazgeç</a><?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Ad</th><th>Kaynak</th><th>Puan</th><th>Yorum</th><th>Dil</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr class="empty-row"><td colspan="7">Kayıt yok.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong></td>
                    <td><span class="badge badge-gray"><?= e($sources[$r['source']] ?? $r['source']) ?></span></td>
                    <td><?= str_repeat('★', (int) $r['rating']) ?></td>
                    <td><?= e(excerpt_of($r['comment'], 50)) ?></td>
                    <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                    <td><?= status_badge((string) $r['status']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('testimonials.php', ['id' => $r['id']])) ?>"><?= icon('edit', 'icon-sm') ?> Düzenle</a>
                            <form method="post" action="<?= e(admin_url('testimonials.php')) ?>" data-confirm="Yorum silinsin mi?">
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
<?php require __DIR__ . '/partials/footer.php'; ?>
