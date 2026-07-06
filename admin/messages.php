<?php
require_once __DIR__ . '/partials/top.php';

$action = get_param('action', 'list');
$id = int_param('id');
$statusFilter = get_param('status');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = post('do');

    if ($do === 'delete') {
        Database::delete('contact_messages', int_param('id'));
        flash_set('success', 'Mesaj silindi.');
        admin_redirect('messages.php');
    }
    if ($do === 'toggle') {
        $m = Database::row('SELECT status FROM contact_messages WHERE id = ?', [int_param('id')]);
        if ($m) {
            Database::update('contact_messages', int_param('id'), [
                'status' => $m['status'] === 'new' ? 'read' : 'new',
            ]);
        }
        admin_redirect('messages.php');
    }
}

$pageTitle = 'İletişim Mesajları';
$active = 'messages';
require __DIR__ . '/partials/header.php';

if ($action === 'view' && $id):
    $m = Database::row('SELECT * FROM contact_messages WHERE id = ?', [$id]);
    if (!$m) {
        echo '<div class="alert alert-danger">Kayıt bulunamadı.</div>';
        require __DIR__ . '/partials/footer.php';
        exit;
    }
    if ($m['status'] === 'new') {
        Database::update('contact_messages', $id, ['status' => 'read']);
        $m['status'] = 'read';
    }
?>
<div class="page-head">
    <h1>Mesaj #<?= (int) $m['id'] ?> — <?= e($m['full_name']) ?></h1>
    <a class="btn btn-ghost" href="<?= e(admin_url('messages.php')) ?>">← Listeye Dön</a>
</div>
<div class="panel">
    <dl class="detail-grid">
        <div><dt>Ad Soyad</dt><dd><?= e($m['full_name']) ?></dd></div>
        <div><dt>Telefon</dt><dd><?= e($m['phone'] ?: '—') ?></dd></div>
        <div><dt>E-posta</dt><dd><?= e($m['email'] ?: '—') ?></dd></div>
        <div><dt>Tarih</dt><dd><?= e(format_date($m['created_at'], true)) ?></dd></div>
        <div class="full"><dt>Konu</dt><dd><?= e($m['subject'] ?: '—') ?></dd></div>
        <div class="full"><dt>Mesaj</dt><dd><?= nl2br(e($m['message'])) ?></dd></div>
    </dl>
    <div class="form-actions">
        <?php if ($m['phone']): ?>
        <a class="btn btn-wa" href="<?= e(admin_wa_link($m['phone'], 'Merhaba ' . $m['full_name'] . ', D4stattoo — mesajınız için teşekkürler. ')) ?>" target="_blank" rel="noopener">WhatsApp ile Yanıtla</a>
        <?php endif; ?>
        <?php if ($m['email']): ?>
        <a class="btn btn-ghost" href="mailto:<?= e($m['email']) ?>">E-posta ile Yanıtla</a>
        <?php endif; ?>
    </div>
</div>
<?php else:
    $where = in_array($statusFilter, ['new', 'read'], true)
        ? 'WHERE status = ' . Database::pdo()->quote($statusFilter) : '';
    $rows = Database::all("SELECT * FROM contact_messages $where ORDER BY created_at DESC");
?>
<div class="page-head">
    <div>
        <h1>İletişim Mesajları</h1>
        <p>İletişim formundan gelen mesajlar.</p>
    </div>
</div>
<form class="filter-bar" method="get" action="">
    <select name="status">
        <option value="">Tümü</option>
        <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>Bekleyen</option>
        <option value="read" <?= $statusFilter === 'read' ? 'selected' : '' ?>>Okundu</option>
    </select>
    <button class="btn btn-sm" type="submit"><?= icon('filter', 'icon-sm') ?> Filtrele</button>
</form>
<div class="table-wrap">
    <table>
        <thead><tr><th>Ad Soyad</th><th>Konu</th><th>Telefon</th><th>Durum</th><th>Tarih</th><th></th></tr></thead>
        <tbody>
        <?php if (!$rows): ?><tr class="empty-row"><td colspan="6">Mesaj yok.</td></tr><?php endif; ?>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><strong><?= e($r['full_name']) ?></strong></td>
                <td><?= e(excerpt_of($r['subject'] ?: $r['message'], 50)) ?></td>
                <td><?= e($r['phone'] ?: '—') ?></td>
                <td><?= status_badge($r['status']) ?></td>
                <td><?= e(format_date($r['created_at'], true)) ?></td>
                <td>
                    <div class="row-actions">
                        <a class="btn btn-sm btn-ghost" href="<?= e(admin_url('messages.php', ['action' => 'view', 'id' => $r['id']])) ?>"><?= icon('eye', 'icon-sm') ?> Oku</a>
                        <form method="post" action="<?= e(admin_url('messages.php')) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="do" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button class="btn btn-sm" type="submit"><?= $r['status'] === 'new' ? 'Okundu Yap' : 'Bekliyor Yap' ?></button>
                        </form>
                        <form method="post" action="<?= e(admin_url('messages.php')) ?>" data-confirm="Bu mesaj silinsin mi?">
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
