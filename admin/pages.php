<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$langFilter = preg_replace('/[^a-z]/', '', get_param('lang'));

// --- POST işlemleri ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('pages', int_param('id'));
        flash_set('success', 'Sayfa silindi.');
        admin_redirect('pages.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'             => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'title'            => post('title'),
            'body'             => clean_html((string) ($_POST['body'] ?? '')),
            'meta_title'       => post('meta_title'),
            'meta_description' => post('meta_description'),
            'status'           => post('status') === '1' ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Başlık zorunludur.');
            admin_redirect('pages.php', $id ? ['action' => 'edit', 'id' => $id] : ['action' => 'new']);
        }
        $slug = post('slug') !== '' ? slugify(post('slug')) : slugify($data['title']);
        $data['slug'] = unique_slug('pages', $slug, $data['lang'], $id);

        if ($id) {
            Database::update('pages', $id, $data);
            flash_set('success', 'Sayfa güncellendi.');
        } else {
            $id = Database::insert('pages', $data);
            flash_set('success', 'Sayfa eklendi.');
        }
        admin_redirect('pages.php', ['action' => 'edit', 'id' => $id]);
    }
}

$pageTitle = 'Sayfa Yönetimi';
$active = 'pages';
require __DIR__ . '/partials/header.php';

// --- Form (yeni / düzenle) ----------------------------------------------------
if ($action === 'new' || $action === 'edit'):
    $row = $id ? Database::row('SELECT * FROM pages WHERE id = ?', [$id]) : null;
    if ($action === 'edit' && !$row) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
?>
<div class="page-head">
    <h1><?= $row ? 'Sayfayı Düzenle' : 'Yeni Sayfa' ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('pages.php')) ?>">← Listeye Dön</a>
</div>
<form method="post" action="<?= e(admin_url('pages.php', $id ? ['id' => $id] : [])) ?>">
    <?= Csrf::field() ?>
    <input type="hidden" name="do" value="save">
    <div class="panel">
        <div class="form-grid">
            <div class="form-group">
                <label for="title">Başlık *</label>
                <input id="title" type="text" name="title" required maxlength="190" value="<?= e($row['title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL)</label>
                <input id="slug" type="text" name="slug" maxlength="190" value="<?= e($row['slug'] ?? '') ?>" placeholder="boş bırakılırsa başlıktan üretilir">
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
            <div class="form-group full">
                <label for="body">İçerik (HTML)</label>
                <textarea id="body" name="body" class="editor"><?= e($row['body'] ?? '') ?></textarea>
                <span class="hint">İzin verilen etiketler: p, h2-h4, ul/ol/li, strong, em, a, img, blockquote, table…</span>
            </div>
            <div class="form-group">
                <label for="meta_title">Meta Title (SEO)</label>
                <input id="meta_title" type="text" name="meta_title" maxlength="190" value="<?= e($row['meta_title'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="meta_description">Meta Description (SEO)</label>
                <input id="meta_description" type="text" name="meta_description" maxlength="300" value="<?= e($row['meta_description'] ?? '') ?>">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <?php if ($row): ?>
            <a class="btn btn-ghost" href="<?= e(url($row['slug'], $row['lang'])) ?>" target="_blank">Sayfayı Gör ↗</a>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php else: // --- Liste --------------------------------------------------------
    $where = $langFilter ? ' WHERE lang = ' . Database::pdo()->quote($langFilter) : '';
    $rows = Database::all("SELECT * FROM pages $where ORDER BY lang, title");
?>
<div class="page-head">
    <div>
        <h1>Sayfalar</h1>
        <p>Hakkımızda, hijyen, KVKK gibi içerik sayfaları.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(admin_url('pages.php', ['action' => 'new'])) ?>">+ Yeni Sayfa</a>
</div>
<form class="filter-bar" method="get" action="">
    <?= lang_select('lang', $langFilter, true) ?>
    <button class="btn btn-sm" type="submit">Filtrele</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Başlık</th><th>Slug</th><th>Dil</th><th>Durum</th><th>Güncelleme</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="6">Kayıt yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?= e($r['title']) ?></strong></td>
                <td>/<?= e($r['slug']) ?></td>
                <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                <td><?= status_badge((string) $r['status']) ?></td>
                <td><?= e(format_date($r['updated_at'])) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('pages.php', ['action' => 'edit', 'id' => $r['id']])) ?>">Düzenle</a>
                        <form method="post" action="<?= e(admin_url('pages.php')) ?>" data-confirm="Bu sayfa silinsin mi?">
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
