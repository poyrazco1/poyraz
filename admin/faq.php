<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');
$langFilter = preg_replace('/[^a-z]/', '', get_param('lang'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('faq_items', int_param('id'));
        flash_set('success', 'Soru silindi.');
        admin_redirect('faq.php');
    }

    if ($do === 'save') {
        $data = [
            'lang'       => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'question'   => post('question'),
            'answer'     => post('answer'),
            'sort_order' => int_param('sort_order'),
            'status'     => post('status') === '1' ? 1 : 0,
        ];
        if ($data['question'] === '' || $data['answer'] === '') {
            flash_set('error', 'Soru ve cevap alanları zorunludur.');
            admin_redirect('faq.php', $id ? ['id' => $id] : []);
        }
        if ($id) {
            Database::update('faq_items', $id, $data);
            flash_set('success', 'Soru güncellendi.');
        } else {
            Database::insert('faq_items', $data);
            flash_set('success', 'Soru eklendi.');
        }
        admin_redirect('faq.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM faq_items WHERE id = ?', [$id]) : null;
$where = $langFilter ? ' WHERE lang = ' . Database::pdo()->quote($langFilter) : '';
$rows = Database::all("SELECT * FROM faq_items $where ORDER BY lang, sort_order");

$pageTitle = 'SSS Yönetimi';
$active = 'faq';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <h1>Sık Sorulan Sorular</h1>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Soruyu Düzenle' : 'Yeni Soru' ?></h2>
        <form method="post" action="<?= e(admin_url('faq.php', $edit ? ['id' => $edit['id']] : [])) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="question">Soru *</label>
                    <input id="question" type="text" name="question" required maxlength="300" value="<?= e($edit['question'] ?? '') ?>">
                </div>
                <div class="form-group full">
                    <label for="answer">Cevap *</label>
                    <textarea id="answer" name="answer" required><?= e($edit['answer'] ?? '') ?></textarea>
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
                <?php if ($edit): ?><a class="btn btn-ghost" href="<?= e(admin_url('faq.php')) ?>">Vazgeç</a><?php endif; ?>
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
                <thead><tr><th>Soru</th><th>Dil</th><th>Sıra</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php if (!$rows): ?><tr class="empty-row"><td colspan="5">Kayıt yok.</td></tr><?php endif; ?>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e(excerpt_of($r['question'], 60)) ?></td>
                        <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                        <td><?= (int) $r['sort_order'] ?></td>
                        <td><?= status_badge((string) $r['status']) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('faq.php', ['id' => $r['id']])) ?>"><?= icon('edit', 'icon-sm') ?> Düzenle</a>
                                <form method="post" action="<?= e(admin_url('faq.php')) ?>" data-confirm="Soru silinsin mi?">
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
