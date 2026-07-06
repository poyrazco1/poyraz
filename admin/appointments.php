<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$statusFilter = get_param('status');
$q = get_param('q');

$statuses = ['new' => 'Yeni', 'confirmed' => 'Onaylandı', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        $row = Database::row('SELECT reference_image FROM appointments WHERE id = ?', [int_param('id')]);
        if ($row) {
            delete_upload($row['reference_image']);
        }
        Database::delete('appointments', int_param('id'));
        flash_set('success', 'Randevu kaydı silindi.');
        admin_redirect('appointments.php');
    }

    if ($do === 'update' && $id) {
        $status = array_key_exists(post('status'), $statuses) ? post('status') : 'new';
        Database::update('appointments', $id, [
            'status'     => $status,
            'admin_note' => post('admin_note'),
        ]);
        flash_set('success', 'Randevu güncellendi.');
        admin_redirect('appointments.php', ['action' => 'view', 'id' => $id]);
    }
}

$pageTitle = 'Randevular';
$active = 'appointments';
require __DIR__ . '/partials/header.php';

if ($action === 'view' && $id):
    $a = Database::row('SELECT * FROM appointments WHERE id = ?', [$id]);
    if (!$a) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
    $prefText = format_preferred($a['preferred_datetime']);
    $waMsg = 'Merhaba ' . $a['full_name'] . ', D4stattoo randevu talebiniz bize ulaştı. '
           . ($prefText !== 'Tarih/saat seçilmemiş' ? 'Talep ettiğiniz tarih/saat: ' . $prefText . '. ' : '')
           . 'Dövme stiliniz: ' . ($a['style'] ?: 'dövme') . ', bölge: ' . ($a['tattoo_area'] ?: '-')
           . ($a['tattoo_size'] ? ', ölçü: ' . $a['tattoo_size'] : '')
           . '. Uygunluk için size dönüş yapacağız.';
    $colorLabels = ['color' => 'Renkli', 'blackgray' => 'Siyah/Gri', 'undecided' => 'Kararsız'];
?>
<div class="page-head">
    <h1>Randevu #<?= (int) $a['id'] ?> — <?= e($a['full_name']) ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('appointments.php')) ?>">← Listeye Dön</a>
</div>
<div class="two-col">
    <div class="panel">
        <h2>Talep Detayı</h2>
        <dl class="detail-grid">
            <div><dt>Ad Soyad</dt><dd><?= e($a['full_name']) ?></dd></div>
            <div><dt>Telefon</dt><dd><a href="tel:<?= e($a['phone']) ?>"><?= e($a['phone']) ?></a></dd></div>
            <div><dt>E-posta</dt><dd><?= e($a['email'] ?: '—') ?></dd></div>
            <div><dt>Bölge</dt><dd><?= e($a['tattoo_area'] ?: '—') ?></dd></div>
            <div><dt>Ölçü</dt><dd><?= e($a['tattoo_size'] ?: '—') ?></dd></div>
            <div><dt>Renk</dt><dd><?= e($colorLabels[$a['color_type']] ?? '—') ?></dd></div>
            <div><dt>Stil</dt><dd><?= e($a['style'] ?: '—') ?></dd></div>
            <div><dt>Uygun Gün/Saat</dt><dd><strong><?= e(format_preferred($a['preferred_datetime'])) ?></strong></dd></div>
            <div><dt>Daha Önce Dövme</dt><dd><?= $a['has_previous_tattoo'] ? 'Evet' : 'Hayır' ?></dd></div>
            <div><dt>Tarih</dt><dd><?= e(format_date($a['created_at'], true)) ?></dd></div>
            <div class="full"><dt>Açıklama</dt><dd><?= nl2br(e($a['description'] ?: '—')) ?></dd></div>
            <?php if ($a['reference_image']): ?>
            <div class="full">
                <dt>Referans Görsel</dt>
                <dd><a href="<?= e(upload_url($a['reference_image'])) ?>" target="_blank"><img class="ref-img" src="<?= e(upload_url($a['reference_image'])) ?>" alt="Referans"></a></dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>
    <div>
        <div class="panel">
            <h2>Durum & Not</h2>
            <form method="post" action="<?= e(admin_url('appointments.php', ['id' => $id])) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="do" value="update">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="status">Durum</label>
                        <select id="status" name="status">
                            <?php foreach ($statuses as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $a['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label for="admin_note">Admin Notu</label>
                        <textarea id="admin_note" name="admin_note"><?= e($a['admin_note'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
        <div class="panel">
            <h2>Hızlı İletişim</h2>
            <p style="color:var(--muted);font-size:.85rem">Müşteriye hazır mesajla WhatsApp'tan ulaşın:</p>
            <a class="btn btn-wa btn-block" href="<?= e(admin_wa_link($a['phone'], $waMsg)) ?>" target="_blank" rel="noopener">WhatsApp'tan Mesaj Gönder</a>
        </div>
    </div>
</div>
<?php else:
    $conds = [];
    $params = [];
    if (array_key_exists($statusFilter, $statuses)) { $conds[] = 'status = ?'; $params[] = $statusFilter; }
    if ($q !== '') { $conds[] = '(full_name LIKE ? OR phone LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $rows = Database::all("SELECT * FROM appointments $where ORDER BY created_at DESC", $params);
?>
<div class="page-head">
    <div>
        <h1>Randevu Talepleri</h1>
        <p>Randevu formundan gelen tüm talepler.</p>
    </div>
</div>
<form class="filter-bar" method="get" action="">
    <input type="text" name="q" placeholder="Ad veya telefon ara…" value="<?= e($q) ?>">
    <select name="status">
        <option value="">Tüm Durumlar</option>
        <?php foreach ($statuses as $k => $v): ?>
        <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-sm" type="submit"><?= icon('search', 'icon-sm') ?> Ara</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Ad Soyad</th><th>Telefon</th><th>Bölge</th><th>Randevu Tarihi</th><th>Durum</th><th>Talep</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="7">Randevu talebi yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?= e($r['full_name']) ?></strong></td>
                <td><?= e($r['phone']) ?></td>
                <td><?= e($r['tattoo_area']) ?></td>
                <td><?= e(format_preferred($r['preferred_datetime'])) ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= e(format_date($r['created_at'], true)) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('appointments.php', ['action' => 'view', 'id' => $r['id']])) ?>"><?= icon('eye', 'icon-sm') ?> Görüntüle</a>
                        <form method="post" action="<?= e(admin_url('appointments.php')) ?>" data-confirm="Bu kayıt silinsin mi?">
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
