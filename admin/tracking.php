<?php
require_once __DIR__ . '/partials/top.php';

$id = int_param('id');
$statuses = ['pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'in_progress' => 'Devam Ediyor', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('customer_tracking', int_param('id'));
        flash_set('success', 'Takip kaydı silindi.');
        admin_redirect('tracking.php');
    }

    if ($do === 'save') {
        $data = [
            'full_name'    => post('full_name'),
            'phone'        => post('phone'),
            'service_type' => post('service_type'),
            'status'       => array_key_exists(post('status'), $statuses) ? post('status') : 'pending',
            'public_note'  => post('public_note'),
            'private_note' => post('private_note'),
        ];
        if ($data['full_name'] === '') {
            flash_set('error', 'Ad soyad zorunludur.');
            admin_redirect('tracking.php', $id ? ['id' => $id] : []);
        }
        if ($id) {
            Database::update('customer_tracking', $id, $data);
            flash_set('success', 'Takip kaydı güncellendi.');
        } else {
            // Benzersiz takip kodu üret
            do {
                $code = strtoupper(substr(str_replace(['0', 'O', 'I', '1'], '', bin2hex(random_bytes(8))), 0, 8));
            } while (strlen($code) < 6 || Database::value('SELECT COUNT(*) FROM customer_tracking WHERE tracking_code = ?', [$code]) > 0);
            $data['tracking_code'] = $code;
            Database::insert('customer_tracking', $data);
            flash_set('success', 'Takip kaydı oluşturuldu. Kod: ' . $code);
        }
        admin_redirect('tracking.php');
    }
}

$edit = $id ? Database::row('SELECT * FROM customer_tracking WHERE id = ?', [$id]) : null;
$rows = Database::all('SELECT * FROM customer_tracking ORDER BY updated_at DESC');

$pageTitle = 'Müşteri Takip';
$active = 'tracking';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Müşteri Takip</h1>
        <p>Müşterilerinize takip kodu verin; durumlarını <?= e(url('takip')) ?> adresinden sorgulasınlar.</p>
    </div>
</div>
<div class="two-col">
    <div class="panel">
        <h2><?= $edit ? 'Kaydı Düzenle' : 'Yeni Takip Kaydı' ?></h2>
        <form method="post" action="<?= e(admin_url('tracking.php', $edit ? ['id' => $edit['id']] : [])) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="do" value="save">
            <div class="form-grid">
                <?php if ($edit): ?>
                <div class="form-group full">
                    <label>Takip Kodu</label>
                    <input type="text" value="<?= e($edit['tracking_code']) ?>" readonly>
                    <span class="hint">Public link: <?= e(url('takip/' . $edit['tracking_code'])) ?></span>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="full_name">Ad Soyad *</label>
                    <input id="full_name" type="text" name="full_name" required maxlength="150" value="<?= e($edit['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Telefon</label>
                    <input id="phone" type="text" name="phone" maxlength="30" value="<?= e($edit['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="service_type">İşlem</label>
                    <input id="service_type" type="text" name="service_type" maxlength="150"
                           placeholder="Örn: Kol kaplama — 2. seans" value="<?= e($edit['service_type'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="status">Durum</label>
                    <select id="status" name="status">
                        <?php foreach ($statuses as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($edit['status'] ?? 'pending') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label for="public_note">Müşteriye Açık Not</label>
                    <textarea id="public_note" name="public_note"><?= e($edit['public_note'] ?? '') ?></textarea>
                    <span class="hint">Takip sayfasında müşteri bu notu görür.</span>
                </div>
                <div class="form-group full">
                    <label for="private_note">Özel Not (sadece admin)</label>
                    <textarea id="private_note" name="private_note"><?= e($edit['private_note'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $edit ? 'Güncelle' : 'Kod Oluştur' ?></button>
                <?php if ($edit): ?>
                <a class="btn btn-ghost" href="<?= e(admin_url('tracking.php')) ?>">Vazgeç</a>
                <?php if ($edit['phone']): ?>
                <a class="btn btn-wa" target="_blank" rel="noopener"
                   href="<?= e(admin_wa_link($edit['phone'], 'Merhaba ' . $edit['full_name'] . ', D4stattoo işlem takip kodunuz: ' . $edit['tracking_code'] . ' — Durumunuzu buradan izleyebilirsiniz: ' . url('takip/' . $edit['tracking_code']))) ?>">Kodu WhatsApp'la Gönder</a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Kod</th><th>Ad Soyad</th><th>İşlem</th><th>Durum</th><th>Güncelleme</th><th></th></tr></thead>
            <tbody>
            <?php if (!$rows): ?><tr class="empty-row"><td colspan="6">Kayıt yok.</td></tr><?php endif; ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><a href="<?= e(url('takip/' . $r['tracking_code'])) ?>" target="_blank"><strong><?= e($r['tracking_code']) ?></strong></a></td>
                    <td><?= e($r['full_name']) ?></td>
                    <td><?= e($r['service_type'] ?: '—') ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td><?= e(format_date($r['updated_at'], true)) ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('tracking.php', ['id' => $r['id']])) ?>"><?= icon('edit', 'icon-sm') ?> Düzenle</a>
                            <form method="post" action="<?= e(admin_url('tracking.php')) ?>" data-confirm="Kayıt silinsin mi?">
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
