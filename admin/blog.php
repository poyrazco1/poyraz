<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$langFilter = preg_replace('/[^a-z]/', '', get_param('lang'));
$q = get_param('q');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $row = Database::row('SELECT cover_image FROM blog_posts WHERE id = ?', [int_param('id')]);
        if ($row) {
            delete_upload($row['cover_image']);
        }
        Database::delete('blog_posts', int_param('id'));
        flash_set('success', 'Yazı silindi.');
        admin_redirect('blog.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'             => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'title'            => post('title'),
            'excerpt'          => post('excerpt'),
            'content'          => clean_html((string) ($_POST['content'] ?? '')),
            'category_id'      => int_param('category_id') ?: null,
            'meta_title'       => post('meta_title'),
            'meta_description' => post('meta_description'),
            'tags'             => post('tags'),
            'status'           => post('status') === '1' ? 1 : 0,
            'published_at'     => post('published_at') !== '' && strtotime(post('published_at')) !== false
                                  ? date('Y-m-d H:i:s', strtotime(post('published_at')))
                                  : date('Y-m-d H:i:s'),
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Başlık zorunludur.');
            admin_redirect('blog.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }
        $slug = post('slug') !== '' ? slugify(post('slug')) : slugify($data['title']);
        $data['slug'] = unique_slug('blog_posts', $slug, $data['lang'], $id);

        if (!empty($_FILES['cover_image']['name'])) {
            $up = upload_image($_FILES['cover_image'], 'blog');
            if ($up['ok'] && $up['path']) {
                if ($id) {
                    $old = Database::row('SELECT cover_image FROM blog_posts WHERE id = ?', [$id]);
                    if ($old) delete_upload($old['cover_image']);
                }
                $data['cover_image'] = $up['path'];
            } elseif (!$up['ok']) {
                flash_set('error', 'Kapak görseli: ' . $up['error']);
                admin_redirect('blog.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
            }
        }

        if ($id) {
            Database::update('blog_posts', $id, $data);
            flash_set('success', 'Yazı güncellendi.');
        } else {
            $id = Database::insert('blog_posts', $data);
            flash_set('success', 'Yazı eklendi.');
        }
        admin_redirect('blog.php', ['action' => 'edit', 'id' => $id]);
    }
}

$pageTitle = 'Blog Yönetimi';
$active = 'blog';
require __DIR__ . '/partials/header.php';

if ($action === 'new' || $action === 'edit'):
    $row = $id ? Database::row('SELECT * FROM blog_posts WHERE id = ?', [$id]) : null;
    if ($action === 'edit' && !$row) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
    $cats = Database::all('SELECT * FROM blog_categories ORDER BY lang, name');
?>
<div class="page-head">
    <h1><?= $row ? 'Yazıyı Düzenle' : 'Yeni Yazı' ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('blog.php')) ?>">← Listeye Dön</a>
</div>
<form method="post" action="<?= e(admin_url('blog.php', $id ? ['id' => $id] : [])) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
    <input type="hidden" name="do" value="save">
    <div class="panel">
        <div class="form-grid">
            <div class="form-group full">
                <label for="title">Başlık *</label>
                <input id="title" type="text" name="title" required maxlength="190" value="<?= e($row['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input id="slug" type="text" name="slug" maxlength="190" value="<?= e($row['slug'] ?? '') ?>" placeholder="/blog/{slug}">
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
                <label for="lang">Dil</label>
                <?= lang_select('lang', $row['lang'] ?? 'tr') ?>
            </div>
            <div class="form-group">
                <label for="status">Durum</label>
                <select id="status" name="status">
                    <option value="1" <?= ($row['status'] ?? 1) == 1 ? 'selected' : '' ?>>Yayında</option>
                    <option value="0" <?= ($row['status'] ?? 1) == 0 ? 'selected' : '' ?>>Taslak</option>
                </select>
            </div>
            <div class="form-group">
                <label for="published_at">Yayın Tarihi</label>
                <input id="published_at" type="datetime-local" name="published_at"
                       value="<?= e(!empty($row['published_at']) ? date('Y-m-d\TH:i', strtotime($row['published_at'])) : date('Y-m-d\TH:i')) ?>">
            </div>
            <div class="form-group">
                <label for="tags">Etiketler (virgülle)</label>
                <input id="tags" type="text" name="tags" maxlength="300" value="<?= e($row['tags'] ?? '') ?>">
            </div>
            <div class="form-group full">
                <label for="excerpt">Özet</label>
                <textarea id="excerpt" name="excerpt" maxlength="500" style="min-height:70px"><?= e($row['excerpt'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
                <label for="content">İçerik (HTML)</label>
                <textarea id="content" name="content" class="editor"><?= e($row['content'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="cover_image">Kapak Görseli</label>
                <input id="cover_image" type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp">
                <?php if (!empty($row['cover_image'])): ?>
                <div class="current-img"><img src="<?= e(upload_url($row['cover_image'])) ?>" alt=""></div>
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
            <a class="btn btn-ghost" href="<?= e(url('blog/' . $row['slug'], $row['lang'])) ?>" target="_blank">Yazıyı Gör ↗</a>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php else:
    $conds = [];
    $params = [];
    if ($langFilter) { $conds[] = 'p.lang = ?'; $params[] = $langFilter; }
    if ($q !== '') { $conds[] = 'p.title LIKE ?'; $params[] = '%' . $q . '%'; }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $rows = Database::all(
        "SELECT p.*, c.name AS cat_name FROM blog_posts p
         LEFT JOIN blog_categories c ON c.id = p.category_id
         $where ORDER BY p.published_at DESC", $params
    );
?>
<div class="page-head">
    <div>
        <h1>Blog Yazıları</h1>
        <p>Blog ve Akademi içerikleri. Akademi sayfası, "Akademi" kategorisindeki yazıları listeler.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(admin_url('blog.php', ['action' => 'new'])) ?>">+ Yeni Yazı</a>
</div>
<form class="filter-bar" method="get" action="">
    <input type="text" name="q" placeholder="Başlıkta ara…" value="<?= e($q) ?>">
    <?= lang_select('lang', $langFilter, true) ?>
    <button class="btn btn-sm" type="submit">Ara</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Kapak</th><th>Başlık</th><th>Kategori</th><th>Dil</th><th>Durum</th><th>Yayın</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="7">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><img class="thumb" src="<?= e(upload_url($r['cover_image'])) ?>" alt=""></td>
                <td><strong><?= e($r['title']) ?></strong></td>
                <td><?= e($r['cat_name'] ?? '—') ?></td>
                <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                <td><?= status_badge((string) $r['status']) ?></td>
                <td><?= e(format_date($r['published_at'])) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('blog.php', ['action' => 'edit', 'id' => $r['id']])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(admin_url('blog.php')) ?>" data-confirm="Bu yazı silinsin mi?">
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
