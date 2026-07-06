<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $delId = int_param('id');
        Database::run('UPDATE blog_posts SET category_id = NULL WHERE category_id = ?', [$delId]);
        Database::delete('blog_categories', $delId);
        flash_set('success', 'Kategori silindi (yazıları kategorisiz bırakıldı).');
        admin_redirect('blog-categories.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'   => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'name'   => post('name'),
            'status' => post('status') === '1' ? 1 : 0,
        ];
        if ($data['name'] === '') {
            flash_set('error', 'Kategori adı zorunludur.');
            admin_redirect('blog-categories.php');
        }
        $slug = post('slug') !== '' ? slugify(post('slug')) : slugify($data['name']);
        $data['slug'] = unique_slug('blog_categories', $slug, $data['lang'], $id);

        if ($id) {
            Database::update('blog_categories', $id, $data);
            flash_set('success', 'Kategori güncellendi.');
        } else {
            Database::insert('blog_categories', $data);
            flash_set('success', 'Kategori eklendi.');
        }
        admin_redirect('blog-categories.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM blog_categories WHERE id = ?', [$id]) : null;
$rows = Database::all('SELECT c.*, (SELECT COUNT(*) FROM blog_posts p WHERE p.category_id = c.id) AS post_count FROM blog_categories c ORDER BY lang, name');

$pageTitle = 'Blog Kategorileri';
$active = 'blog-categories';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <h1>Blog Kategorileri</h1>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Kategoriyi Düzenle' : 'Yeni Kategori' ?></h2>
        <form method="post" action="<?= e(admin_url('blog-categories.php', $edit ? ['id' => $edit['id']] : [])) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Ad *</label>
                    <input id="name" type="text" name="name" required maxlength="150" value="<?= e($edit['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input id="slug" type="text" name="slug" maxlength="190" value="<?= e($edit['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="lang">Dil</label>
                    <?= lang_select('lang', $edit['lang'] ?? 'tr') ?>
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
                <?php if ($edit): ?><a class="btn btn-ghost" href="<?= e(admin_url('blog-categories.php')) ?>">Vazgeç</a><?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Ad</th><th>Slug</th><th>Dil</th><th>Yazı</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr class="empty-row"><td colspan="6">Kayıt yok.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong></td>
                    <td><?= e($r['slug']) ?></td>
                    <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                    <td><?= (int) $r['post_count'] ?></td>
                    <td><?= status_badge((string) $r['status']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('blog-categories.php', ['id' => $r['id']])) ?>">Düzenle</a>
                            <form method="post" action="<?= e(admin_url('blog-categories.php')) ?>" data-confirm="Kategori silinsin mi?">
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
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
