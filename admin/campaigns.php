<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('campaigns', int_param('id'));
        flash_set('success', 'Kampanya silindi.');
        admin_redirect('campaigns.php');
    }

    if ($do === 'toggle_band') {
        set_setting('campaign_show', post('campaign_show') === '1' ? '1' : '0', 'tr');
        flash_set('success', 'Kampanya bandı ayarı güncellendi.');
        admin_redirect('campaigns.php');
    }

    if ($do === 'save') {
        $start = post('start_date');
        $end = post('end_date');
        $data = [
            'lang'        => preg_replace('/[^a-z]/', '', post('lang', 'tr')) ?: 'tr',
            'title'       => post('title'),
            'description' => post('description'),
            'button_text' => post('button_text'),
            'button_url'  => post('button_url'),
            'start_date'  => $start !== '' && strtotime($start) !== false ? date('Y-m-d', strtotime($start)) : null,
            'end_date'    => $end !== '' && strtotime($end) !== false ? date('Y-m-d', strtotime($end)) : null,
            'status'      => post('status') === '1' ? 1 : 0,
        ];
        if ($data['title'] === '') {
            flash_set('error', 'Başlık zorunludur.');
            admin_redirect('campaigns.php', $id ? ['id' => $id] : []);
        }
        if ($id) {
            Database::update('campaigns', $id, $data);
            flash_set('success', 'Kampanya güncellendi.');
        } else {
            Database::insert('campaigns', $data);
            flash_set('success', 'Kampanya eklendi.');
        }
        admin_redirect('campaigns.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM campaigns WHERE id = ?', [$id]) : null;
$rows = Database::all('SELECT * FROM campaigns ORDER BY lang, id DESC');
$bandOn = setting('campaign_show', '1', 'tr') === '1';

$pageTitle = 'Kampanyalar';
$active = 'campaigns';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Kampanyalar</h1>
        <p>Aktif ve tarih aralığı uygun olan son kampanya, ana sayfanın üstünde bant olarak gösterilir.</p>
    </div>
    <form method="post" action="<?= e(admin_url('campaigns.php')) ?>">
        <?= Csrf::field() ?>
        <input type="hidden" name="do" value="toggle_band">
        <input type="hidden" name="campaign_show" value="<?= $bandOn ? '0' : '1' ?>">
        <button class="btn <?= $bandOn ? 'btn-danger' : 'btn-primary' ?>" type="submit">
            <?= $bandOn ? 'Ana Sayfada Gizle' : 'Ana Sayfada Göster' ?>
        </button>
    </form>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Kampanyayı Düzenle' : 'Yeni Kampanya' ?></h2>
        <form method="post" action="<?= e(admin_url('campaigns.php', $edit ? ['id' => $edit['id']] : [])) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="title">Başlık *</label>
                    <input id="title" type="text" name="title" required maxlength="190" value="<?= e($edit['title'] ?? '') ?>">
                </div>
                <div class="form-group full">
                    <label for="description">Açıklama</label>
                    <textarea id="description" name="description"><?= e($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="button_text">Buton Yazısı</label>
                    <input id="button_text" type="text" name="button_text" maxlength="100" value="<?= e($edit['button_text'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="button_url">Buton Linki</label>
                    <input id="button_url" type="text" name="button_url" maxlength="255" placeholder="/randevu-al" value="<?= e($edit['button_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="start_date">Başlangıç</label>
                    <input id="start_date" type="date" name="start_date" value="<?= e($edit['start_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="end_date">Bitiş</label>
                    <input id="end_date" type="date" name="end_date" value="<?= e($edit['end_date'] ?? '') ?>">
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
                <?php if ($edit): ?><a class="btn btn-ghost" href="<?= e(admin_url('campaigns.php')) ?>">Vazgeç</a><?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Başlık</th><th>Tarih Aralığı</th><th>Dil</th><th>Durum</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr class="empty-row"><td colspan="5">Kayıt yok.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['title']) ?></strong></td>
                    <td><?= e(($r['start_date'] ?: '—') . ' → ' . ($r['end_date'] ?: '—')) ?></td>
                    <td><span class="badge badge-blue"><?= e(strtoupper($r['lang'])) ?></span></td>
                    <td><?= status_badge((string) $r['status']) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('campaigns.php', ['id' => $r['id']])) ?>">Düzenle</a>
                            <form method="post" action="<?= e(admin_url('campaigns.php')) ?>" data-confirm="Kampanya silinsin mi?">
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
